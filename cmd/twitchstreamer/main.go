package main

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

const (
	apiURL = "https://api.twitch.tv/helix"
	idURL  = "https://id.twitch.tv/oauth2"
)

// Set at release build time with -ldflags. This identifier is public.
var defaultClientID string

type token struct {
	AccessToken  string `json:"access_token"`
	RefreshToken string `json:"refresh_token"`
	ExpiresAt    int64  `json:"expires_at"`
}
type app struct {
	client   *http.Client
	clientID string
	dataDir  string
	cacheDir string
	token    token
}
type item struct {
	Title    string `json:"title"`
	Subtitle string `json:"subtitle,omitempty"`
	Arg      string `json:"arg,omitempty"`
	Valid    bool   `json:"valid"`
	Icon     *icon  `json:"icon,omitempty"`
}
type icon struct {
	Path string `json:"path"`
}
type result struct {
	Items []item `json:"items"`
}
type stream struct {
	UserLogin   string `json:"user_login"`
	UserName    string `json:"user_name"`
	Title       string `json:"title"`
	GameID      string `json:"game_id"`
	GameName    string `json:"game_name"`
	ViewerCount int    `json:"viewer_count"`
}
type game struct {
	ID        string `json:"id"`
	Name      string `json:"name"`
	BoxArtURL string `json:"box_art_url"`
}
type channel struct {
	BroadcasterLogin string `json:"broadcaster_login"`
	DisplayName      string `json:"display_name"`
	Title            string `json:"title"`
	GameID           string `json:"game_id"`
	GameName         string `json:"game_name"`
}

func main() {
	a := newApp()
	if len(os.Args) < 2 {
		usage()
		os.Exit(2)
	}
	timeout := 4 * time.Minute
	if os.Args[1] == "auth" {
		timeout = 35 * time.Minute
	}
	ctx, cancel := context.WithTimeout(context.Background(), timeout)
	defer cancel()
	var err error
	switch os.Args[1] {
	case "filter":
		err = a.filter(ctx, os.Args[2:])
	case "auth":
		err = a.auth(ctx)
	case "play":
		err = play(os.Args[2:])
	case "covers":
		err = a.covers(ctx)
	default:
		usage()
		os.Exit(2)
	}
	if err != nil {
		if os.Args[1] == "auth" {
			a.notify("Twitch-Anmeldung fehlgeschlagen. Details im Workflow-Debugger.")
		}
		fmt.Fprintln(os.Stderr, err)
		os.Exit(1)
	}
}
func usage() {
	fmt.Fprintln(os.Stderr, "usage: twitchstreamer filter [top|search|games|gameid|covers] [query] | auth | play URL | covers")
}
func newApp() *app {
	home, _ := os.UserHomeDir()
	data := os.Getenv("alfred_workflow_data")
	if data == "" {
		data = filepath.Join(home, "Library/Application Support/Alfred/Workflow Data/com.eusi.alfred.twitchstreamer")
	}
	cache := os.Getenv("alfred_workflow_cache")
	if cache == "" {
		cache = filepath.Join(home, "Library/Caches/com.runningwithcrayons.Alfred/Workflow Data/com.eusi.alfred.twitchstreamer")
	}
	clientID := strings.TrimSpace(os.Getenv("TWITCH_CLIENT_ID"))
	if clientID == "" {
		clientID = defaultClientID
	}
	return &app{client: &http.Client{Timeout: 15 * time.Second}, clientID: clientID, dataDir: data, cacheDir: cache}
}
func (a *app) tokenPath() string { return filepath.Join(a.dataDir, "token.json") }
func (a *app) loadToken() error {
	b, err := os.ReadFile(a.tokenPath())
	if errors.Is(err, os.ErrNotExist) {
		return nil
	}
	if err != nil {
		return err
	}
	return json.Unmarshal(b, &a.token)
}
func atomicWrite(path string, b []byte) error {
	if err := os.MkdirAll(filepath.Dir(path), 0700); err != nil {
		return err
	}
	f, err := os.CreateTemp(filepath.Dir(path), ".tmp-*")
	if err != nil {
		return err
	}
	defer os.Remove(f.Name())
	if err = f.Chmod(0600); err != nil {
		f.Close()
		return err
	}
	if _, err = f.Write(b); err != nil {
		f.Close()
		return err
	}
	if err = f.Sync(); err != nil {
		f.Close()
		return err
	}
	if err = f.Close(); err != nil {
		return err
	}
	return os.Rename(f.Name(), path)
}
func (a *app) saveToken() error {
	b, err := json.Marshal(a.token)
	if err != nil {
		return err
	}
	return atomicWrite(a.tokenPath(), b)
}
func (a *app) postForm(ctx context.Context, path string, values url.Values, out any) (int, error) {
	req, err := http.NewRequestWithContext(ctx, "POST", idURL+path, strings.NewReader(values.Encode()))
	if err != nil {
		return 0, err
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	resp, err := a.client.Do(req)
	if err != nil {
		return 0, err
	}
	defer resp.Body.Close()
	b, err := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	if err != nil {
		return resp.StatusCode, err
	}
	if err = json.Unmarshal(b, out); err != nil {
		return resp.StatusCode, fmt.Errorf("Twitch response: %w", err)
	}
	return resp.StatusCode, nil
}
func (a *app) validate(ctx context.Context) bool {
	if a.token.AccessToken == "" {
		return false
	}
	req, err := http.NewRequestWithContext(ctx, "GET", idURL+"/validate", nil)
	if err != nil {
		return false
	}
	req.Header.Set("Authorization", "OAuth "+a.token.AccessToken)
	resp, err := a.client.Do(req)
	if err != nil {
		return false
	}
	defer resp.Body.Close()
	if resp.StatusCode != 200 {
		return false
	}
	var v struct {
		ClientID  string `json:"client_id"`
		ExpiresIn int    `json:"expires_in"`
	}
	if json.NewDecoder(resp.Body).Decode(&v) != nil {
		return false
	}
	return v.ClientID == a.clientID && v.ExpiresIn > 60
}
func (a *app) refresh(ctx context.Context) error {
	if a.token.RefreshToken == "" {
		return errors.New("Twitch-Anmeldung erforderlich: twauth in Alfred ausführen")
	}
	previousRefresh := a.token.RefreshToken
	var response struct {
		AccessToken  string `json:"access_token"`
		RefreshToken string `json:"refresh_token"`
		ExpiresIn    int    `json:"expires_in"`
		Message      string `json:"message"`
	}
	status, err := a.postForm(ctx, "/token", url.Values{"client_id": {a.clientID}, "grant_type": {"refresh_token"}, "refresh_token": {a.token.RefreshToken}}, &response)
	if err != nil {
		return err
	}
	if status != 200 || response.AccessToken == "" || response.RefreshToken == "" {
		// A parallel Alfred query may already have rotated the one-time refresh token.
		if a.loadToken() == nil && a.token.RefreshToken != previousRefresh && a.validate(ctx) {
			return nil
		}
		return fmt.Errorf("Twitch-Token konnte nicht erneuert werden: %s; twauth in Alfred ausführen", response.Message)
	}
	a.token = token{response.AccessToken, response.RefreshToken, time.Now().Unix() + int64(response.ExpiresIn)}
	return a.saveToken()
}
func (a *app) ready(ctx context.Context) error {
	if a.clientID == "" {
		return errors.New("Öffentliche Twitch-Client-ID fehlt im Build; Entwickler: TWITCH_CLIENT_ID setzen")
	}
	if err := a.loadToken(); err != nil {
		return err
	}
	if a.validate(ctx) {
		return nil
	}
	return a.refresh(ctx)
}
func (a *app) helix(ctx context.Context, path string, out any) error {
	for attempt := 0; attempt < 2; attempt++ {
		req, err := http.NewRequestWithContext(ctx, "GET", apiURL+path, nil)
		if err != nil {
			return err
		}
		req.Header.Set("Client-Id", a.clientID)
		req.Header.Set("Authorization", "Bearer "+a.token.AccessToken)
		resp, err := a.client.Do(req)
		if err != nil {
			return err
		}
		if resp.StatusCode == 401 && attempt == 0 {
			resp.Body.Close()
			if err := a.refresh(ctx); err != nil {
				return err
			}
			continue
		}
		if resp.StatusCode != 200 {
			b, _ := io.ReadAll(io.LimitReader(resp.Body, 512))
			resp.Body.Close()
			return fmt.Errorf("Twitch HTTP %d: %s", resp.StatusCode, strings.TrimSpace(string(b)))
		}
		err = json.NewDecoder(io.LimitReader(resp.Body, 4<<20)).Decode(out)
		resp.Body.Close()
		return err
	}
	return errors.New("Twitch-Anmeldung fehlgeschlagen")
}
func (a *app) auth(ctx context.Context) error {
	if a.clientID == "" {
		return errors.New("Öffentliche Twitch-Client-ID fehlt im Build; Entwickler: TWITCH_CLIENT_ID setzen")
	}
	var device struct {
		DeviceCode      string `json:"device_code"`
		UserCode        string `json:"user_code"`
		VerificationURI string `json:"verification_uri"`
		ExpiresIn       int    `json:"expires_in"`
		Interval        int    `json:"interval"`
		Message         string `json:"message"`
	}
	// The Device Code endpoint requires the field even when no scopes are needed.
	status, err := a.postForm(ctx, "/device", url.Values{"client_id": {a.clientID}, "scopes": {""}}, &device)
	if err != nil {
		return err
	}
	if status != 200 {
		return fmt.Errorf("Device Code: HTTP %d: %s", status, device.Message)
	}
	if device.DeviceCode == "" {
		return errors.New("Twitch lieferte keinen Device Code")
	}
	if device.Interval < 5 {
		device.Interval = 5
	}
	fmt.Printf("Twitch öffnen: %s\nCode eingeben: %s\nWarte auf Freigabe ...\n", device.VerificationURI, device.UserCode)
	verificationURL, err := url.Parse(device.VerificationURI)
	if err != nil || verificationURL.Scheme != "https" || (verificationURL.Hostname() != "www.twitch.tv" && verificationURL.Hostname() != "twitch.tv") {
		return errors.New("Twitch lieferte eine ungültige Bestätigungs-URL")
	}
	clipboard := exec.Command("/usr/bin/pbcopy")
	clipboard.Stdin = strings.NewReader(device.UserCode)
	if err := clipboard.Run(); err != nil {
		fmt.Fprintln(os.Stderr, "Code konnte nicht kopiert werden; bitte den angezeigten Code manuell eingeben:", err)
		a.notify("Twitch-Code im Workflow-Debugger anzeigen und im Browser eingeben.")
	} else {
		a.notify("Twitch-Code kopiert. Anmeldung im Browser bestätigen.")
	}
	if err := exec.Command("/usr/bin/open", device.VerificationURI).Run(); err != nil {
		fmt.Fprintln(os.Stderr, "Browser konnte nicht geöffnet werden; bitte die angezeigte URL manuell öffnen:", err)
	}
	deadline := time.Now().Add(time.Duration(device.ExpiresIn) * time.Second)
	for time.Now().Before(deadline) {
		select {
		case <-ctx.Done():
			return ctx.Err()
		case <-time.After(time.Duration(device.Interval) * time.Second):
		}
		var response struct {
			AccessToken  string `json:"access_token"`
			RefreshToken string `json:"refresh_token"`
			ExpiresIn    int    `json:"expires_in"`
			Message      string `json:"message"`
		}
		status, err = a.postForm(ctx, "/token", url.Values{"client_id": {a.clientID}, "scopes": {""}, "device_code": {device.DeviceCode}, "grant_type": {"urn:ietf:params:oauth:grant-type:device_code"}}, &response)
		if err != nil {
			return err
		}
		if status == 200 {
			if response.AccessToken == "" || response.RefreshToken == "" {
				return errors.New("Twitch lieferte kein erneuerbares Token")
			}
			a.token = token{response.AccessToken, response.RefreshToken, time.Now().Unix() + int64(response.ExpiresIn)}
			if err := a.saveToken(); err != nil {
				return err
			}
			fmt.Println("Twitch-Anmeldung erfolgreich.")
			a.notify("Twitch ist verbunden.")
			return nil
		}
		if response.Message == "authorization_pending" {
			continue
		}
		if response.Message == "slow_down" {
			device.Interval += 5
			continue
		}
		return fmt.Errorf("Twitch-Anmeldung: HTTP %d: %s", status, response.Message)
	}
	return errors.New("Device Code abgelaufen; auth erneut starten")
}

func (a *app) notify(message string) {
	script := "display notification " + strconv.Quote(message) + " with title \"TwitchStreamer\""
	if err := exec.Command("/usr/bin/osascript", "-e", script).Run(); err != nil {
		fmt.Fprintln(os.Stderr, "Benachrichtigung konnte nicht angezeigt werden:", err)
	}
}

func (a *app) filter(ctx context.Context, args []string) error {
	if len(args) == 0 {
		return errors.New("Filtertyp fehlt")
	}
	mode := args[0]
	query := strings.TrimSpace(strings.Join(args[1:], " "))
	items, err := a.list(ctx, mode, query)
	if err != nil {
		items = []item{{Title: "TwitchStreamer", Subtitle: err.Error(), Valid: false, Icon: &icon{"icon.png"}}}
	}
	if len(items) == 0 {
		items = []item{{Title: "Keine Ergebnisse", Subtitle: "Anderen Suchbegriff versuchen", Valid: false, Icon: &icon{"icon.png"}}}
	}
	return json.NewEncoder(os.Stdout).Encode(result{Items: items})
}
func (a *app) list(ctx context.Context, mode, query string) ([]item, error) {
	if mode == "auth" {
		return []item{{Title: "Mit Twitch verbinden", Subtitle: "Enter öffnet Twitch und kopiert den Anmeldecode", Arg: "auth", Valid: true, Icon: &icon{"icon.png"}}}, nil
	}
	if mode == "search" && len([]rune(query)) < 3 {
		return []item{{Title: "Mindestens 3 Zeichen eingeben", Valid: false}}, nil
	}
	if mode == "gameid" && (query == "" || strings.Trim(query, "0123456789") != "") {
		return []item{{Title: "Numerische Game-ID eingeben", Valid: false}}, nil
	}
	if err := a.ready(ctx); err != nil {
		return nil, err
	}
	switch mode {
	case "top", "gameid":
		v := url.Values{"first": {"20"}}
		if mode == "gameid" {
			v.Set("game_id", query)
		}
		var out struct {
			Data []stream `json:"data"`
		}
		if err := a.helix(ctx, "/streams?"+v.Encode(), &out); err != nil {
			return nil, err
		}
		return a.streamItems(ctx, out.Data), nil
	case "search":
		return a.listSearch(ctx, query)
	case "games", "covers":
		first := "15"
		if mode == "covers" {
			first = "100"
		}
		var out struct {
			Data []game `json:"data"`
		}
		if err := a.helix(ctx, "/games/top?first="+first, &out); err != nil {
			return nil, err
		}
		if mode == "covers" {
			n := 0
			for _, g := range out.Data {
				if a.cover(ctx, g.Name, g.BoxArtURL) != "" {
					n++
				}
			}
			return []item{{Title: fmt.Sprintf("%d Cover zwischengespeichert", n), Valid: true, Icon: &icon{"icon.png"}}}, nil
		}
		counts := map[string]int{}
		hasCounts := false
		var streams struct {
			Data []stream `json:"data"`
		}
		if err := a.helix(ctx, "/streams?first=100", &streams); err == nil {
			hasCounts = true
			for _, s := range streams.Data {
				counts[s.GameID] += s.ViewerCount
			}
		}
		items := make([]item, 0, len(out.Data))
		for i, g := range out.Data {
			subtitle := fmt.Sprintf("#%d · Game-ID %s", i+1, g.ID)
			if hasCounts {
				subtitle = fmt.Sprintf("%d+ Zuschauer in Top-Streams · #%d", counts[g.ID], i+1)
			}
			it := item{Title: g.Name, Subtitle: subtitle, Arg: g.ID, Valid: true, Icon: &icon{"icon.png"}}
			if cover := a.cover(ctx, g.Name, g.BoxArtURL); cover != "" {
				it.Icon.Path = cover
			}
			items = append(items, it)
		}
		return items, nil
	}
	return nil, fmt.Errorf("unbekannter Filter: %s", mode)
}
func (a *app) listSearch(ctx context.Context, query string) ([]item, error) {
	v := url.Values{"first": {"30"}, "live_only": {"true"}, "query": {query}}
	var out struct {
		Data []channel `json:"data"`
	}
	if err := a.helix(ctx, "/search/channels?"+v.Encode(), &out); err != nil {
		return nil, err
	}
	logins := make([]string, 0, len(out.Data))
	for _, c := range out.Data {
		logins = append(logins, c.BroadcasterLogin)
	}
	counts := map[string]int{}
	for start := 0; start < len(logins); start += 100 {
		end := start + 100
		if end > len(logins) {
			end = len(logins)
		}
		v := url.Values{}
		for _, login := range logins[start:end] {
			v.Add("user_login", login)
		}
		v.Set("first", "100")
		var streams struct {
			Data []stream `json:"data"`
		}
		if err := a.helix(ctx, "/streams?"+v.Encode(), &streams); err != nil {
			return nil, err
		}
		for _, s := range streams.Data {
			counts[strings.ToLower(s.UserLogin)] = s.ViewerCount
		}
	}
	items := make([]item, 0, len(out.Data))
	for _, c := range out.Data {
		items = append(items, a.streamItem(ctx, c.BroadcasterLogin, c.DisplayName, c.Title, c.GameName, counts[strings.ToLower(c.BroadcasterLogin)]))
	}
	ids := make([]string, 0, len(out.Data))
	for _, c := range out.Data {
		ids = append(ids, c.GameID)
	}
	a.prefetchCovers(ctx, ids)
	for i, c := range out.Data {
		if cover := a.cachedCover(c.GameName); cover != "" {
			items[i].Icon.Path = cover
		}
	}
	return items, nil
}
func (a *app) streamItems(ctx context.Context, streams []stream) []item {
	ids := make([]string, 0, len(streams))
	for _, s := range streams {
		ids = append(ids, s.GameID)
	}
	a.prefetchCovers(ctx, ids)
	items := make([]item, 0, len(streams))
	for _, s := range streams {
		items = append(items, a.streamItem(ctx, s.UserLogin, s.UserName, s.Title, s.GameName, s.ViewerCount))
	}
	return items
}
func (a *app) prefetchCovers(ctx context.Context, ids []string) {
	v := url.Values{}
	seen := map[string]bool{}
	for _, id := range ids {
		if id != "" && !seen[id] {
			v.Add("id", id)
			seen[id] = true
		}
	}
	if len(v["id"]) == 0 {
		return
	}
	var out struct {
		Data []game `json:"data"`
	}
	if a.helix(ctx, "/games?"+v.Encode(), &out) != nil {
		return
	}
	for _, g := range out.Data {
		a.cover(ctx, g.Name, g.BoxArtURL)
	}
}
func (a *app) streamItem(ctx context.Context, login, name, title, gameName string, viewers int) item {
	it := item{Title: name + "   -   " + title, Subtitle: strconv.Itoa(viewers) + " Zuschauer · " + gameName, Arg: "https://www.twitch.tv/" + url.PathEscape(login), Valid: true, Icon: &icon{"icon.png"}}
	if cover := a.cachedCover(gameName); cover != "" {
		it.Icon.Path = cover
	}
	return it
}
func (a *app) coverPath(name string) string {
	return filepath.Join(a.cacheDir, "covers", url.PathEscape(name)+".jpg")
}
func (a *app) cachedCover(name string) string {
	path := a.coverPath(name)
	if _, err := os.Stat(path); err == nil {
		return path
	}
	return ""
}
func (a *app) cover(ctx context.Context, name, source string) string {
	if path := a.cachedCover(name); path != "" {
		return path
	}
	if source == "" {
		return ""
	}
	source = strings.ReplaceAll(strings.ReplaceAll(source, "{width}", "92"), "{height}", "128")
	u, err := url.Parse(source)
	if err != nil || u.Scheme != "https" || u.Host != "static-cdn.jtvnw.net" {
		return ""
	}
	req, err := http.NewRequestWithContext(ctx, "GET", source, nil)
	if err != nil {
		return ""
	}
	resp, err := a.client.Do(req)
	if err != nil {
		return ""
	}
	defer resp.Body.Close()
	if resp.StatusCode != 200 || !strings.HasPrefix(resp.Header.Get("Content-Type"), "image/") {
		return ""
	}
	b, err := io.ReadAll(io.LimitReader(resp.Body, 2<<20))
	if err != nil || len(b) == 0 {
		return ""
	}
	path := a.coverPath(name)
	if atomicWrite(path, b) != nil {
		return ""
	}
	return path
}
func (a *app) covers(ctx context.Context) error {
	items, err := a.list(ctx, "covers", "")
	if err != nil {
		return err
	}
	fmt.Println(items[0].Title)
	return nil
}
func executable(names ...string) string {
	for _, name := range names {
		if path, err := exec.LookPath(name); err == nil {
			return path
		}
	}
	for _, prefix := range []string{"/opt/homebrew/bin", "/usr/local/bin"} {
		for _, name := range names {
			path := filepath.Join(prefix, name)
			if st, err := os.Stat(path); err == nil && st.Mode()&0111 != 0 {
				return path
			}
		}
	}
	return ""
}
func play(args []string) error {
	if len(args) != 1 {
		return errors.New("Stream-URL fehlt")
	}
	u, err := url.Parse(args[0])
	if err != nil || u.Scheme != "https" || u.Host != "www.twitch.tv" || len(strings.Trim(u.Path, "/")) == 0 {
		return errors.New("ungültige Twitch-URL")
	}
	streamlink := executable("streamlink")
	if streamlink != "" {
		for _, p := range []string{"iina", "vlc", "mpv"} {
			player := executable(p)
			if player == "" {
				for _, appName := range []string{strings.ToUpper(p), p} {
					for _, binaryName := range []string{appName, p} {
						candidate := filepath.Join("/Applications", appName+".app", "Contents/MacOS", binaryName)
						if st, err := os.Stat(candidate); err == nil && st.Mode()&0111 != 0 {
							player = candidate
							break
						}
					}
					if player != "" {
						break
					}
				}
			}
			if player != "" {
				cmd := exec.Command(streamlink, "--player", player, args[0], "best")
				if err := cmd.Start(); err == nil {
					return cmd.Process.Release()
				}
			}
		}
	}
	fmt.Fprintln(os.Stderr, "Streamlink oder Player fehlt; öffne Stream im Browser.")
	return exec.Command("open", args[0]).Run()
}
