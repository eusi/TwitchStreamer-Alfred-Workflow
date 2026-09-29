package main

import (
	"context"
	"encoding/json"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"testing"
)

func TestTokenAtomicStorage(t *testing.T) {
	a := &app{dataDir: t.TempDir(), token: token{AccessToken: "access", RefreshToken: "refresh", ExpiresAt: 123}}
	if err := a.saveToken(); err != nil {
		t.Fatal(err)
	}
	st, err := os.Stat(a.tokenPath())
	if err != nil {
		t.Fatal(err)
	}
	if st.Mode().Perm() != 0600 {
		t.Fatalf("token mode = %v", st.Mode().Perm())
	}
	a.token = token{}
	if err := a.loadToken(); err != nil {
		t.Fatal(err)
	}
	if a.token.RefreshToken != "refresh" {
		t.Fatalf("token not restored: %+v", a.token)
	}
	entries, _ := os.ReadDir(a.dataDir)
	if len(entries) != 1 {
		t.Fatalf("unexpected files: %v", entries)
	}
}

func TestClientIDDefaultAndOverride(t *testing.T) {
	previous := defaultClientID
	defaultClientID = "bundled-public-id"
	t.Cleanup(func() { defaultClientID = previous })
	t.Setenv("TWITCH_CLIENT_ID", "")
	if got := newApp().clientID; got != "bundled-public-id" {
		t.Fatalf("default client ID = %q", got)
	}
	t.Setenv("TWITCH_CLIENT_ID", "developer-override")
	if got := newApp().clientID; got != "developer-override" {
		t.Fatalf("override client ID = %q", got)
	}
}

func TestAuthFilterRequiresNoToken(t *testing.T) {
	a := &app{}
	items, err := a.list(context.Background(), "auth", "")
	if err != nil {
		t.Fatal(err)
	}
	if len(items) != 1 || !items[0].Valid || items[0].Title != "Mit Twitch verbinden" || items[0].Arg != "auth" {
		t.Fatalf("auth item = %+v", items)
	}
}

func TestSearchBatchesViewerCounts(t *testing.T) {
	var streamsCalls int
	a := &app{client: &http.Client{}, clientID: "public", cacheDir: t.TempDir(), token: token{AccessToken: "token"}}
	a.client.Transport = transportFunc(func(r *http.Request) (*http.Response, error) {
		body := ""
		switch r.URL.Path {
		case "/helix/search/channels":
			if r.URL.Query().Get("query") != "test name" {
				t.Errorf("query = %q", r.URL.Query().Get("query"))
			}
			body = `{"data":[{"broadcaster_login":"a","display_name":"A","title":"One","game_name":"Game"},{"broadcaster_login":"b","display_name":"B","title":"Two","game_name":"Game"}]}`
		case "/helix/streams":
			streamsCalls++
			if len(r.URL.Query()["user_login"]) != 2 {
				t.Errorf("logins = %v", r.URL.Query()["user_login"])
			}
			body = `{"data":[{"user_login":"a","viewer_count":42},{"user_login":"b","viewer_count":5}]}`
		default:
			t.Errorf("unexpected path: %s", r.URL.Path)
		}
		return &http.Response{StatusCode: 200, Body: io.NopCloser(strings.NewReader(body)), Header: make(http.Header)}, nil
	})
	items, err := a.listSearch(context.Background(), "test name")
	if err != nil {
		t.Fatal(err)
	}
	if streamsCalls != 1 || len(items) != 2 || !strings.Contains(items[0].Subtitle, "42") {
		t.Fatalf("calls=%d items=%+v", streamsCalls, items)
	}
}

type transportFunc func(*http.Request) (*http.Response, error)

func (f transportFunc) RoundTrip(req *http.Request) (*http.Response, error) { return f(req) }

func TestAlfredJSON(t *testing.T) {
	b, err := json.Marshal(result{Items: []item{{Title: "Title", Arg: "https://www.twitch.tv/user", Valid: true}}})
	if err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(string(b), `"items"`) || strings.Contains(string(b), `"xml"`) {
		t.Fatal(string(b))
	}
	if filepath.IsAbs("icon.png") {
		t.Fatal("fallback icon must be relative to workflow")
	}
}
