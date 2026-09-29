#!/bin/sh
set -eu

cd "$(dirname "$0")/.."
command -v go >/dev/null || { echo 'Go 1.22+ fehlt' >&2; exit 1; }
command -v lipo >/dev/null || { echo 'lipo fehlt' >&2; exit 1; }
command -v codesign >/dev/null || { echo 'codesign fehlt' >&2; exit 1; }
command -v zip >/dev/null || { echo 'zip fehlt' >&2; exit 1; }

go test ./...
public_client_id="${TWITCH_PUBLIC_CLIENT_ID:-}"
case "$public_client_id" in
  *[!a-zA-Z0-9]*) echo 'TWITCH_PUBLIC_CLIENT_ID darf nur Buchstaben und Ziffern enthalten' >&2; exit 1 ;;
esac
if [ -z "$public_client_id" ]; then
  echo 'Warnung: keine öffentliche Client-ID eingebettet; dieses Paket ist nur ein Dev-Build.' >&2
fi
linker_flags="-X main.defaultClientID=$public_client_id"
stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT HUP INT TERM
CGO_ENABLED=0 GOOS=darwin GOARCH=arm64 go build -trimpath -ldflags "$linker_flags" -o "$stage/twitchstreamer-arm64" ./cmd/twitchstreamer
CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -trimpath -ldflags "$linker_flags" -o "$stage/twitchstreamer-amd64" ./cmd/twitchstreamer
lipo -create -output "$stage/twitchstreamer" "$stage/twitchstreamer-arm64" "$stage/twitchstreamer-amd64"
chmod 755 "$stage/twitchstreamer"
codesign --force --sign - "$stage/twitchstreamer"
cp src/info.plist src/icon.png "$stage/"
mkdir -p workflow
(cd "$stage" && zip -q -9 TwitchStreamer.alfredworkflow info.plist icon.png twitchstreamer)
mv "$stage/TwitchStreamer.alfredworkflow" workflow/TwitchStreamer.alfredworkflow
echo 'workflow/TwitchStreamer.alfredworkflow erstellt'
