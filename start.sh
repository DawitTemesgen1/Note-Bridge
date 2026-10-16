#!/usr/bin/env bash
# ============================================================
# Start NoteBridge PHP Web Server and open default browser
# ============================================================

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

PHP_BIN="/opt/lampp/bin/php"
if ! [ -x "$PHP_BIN" ]; then
    PHP_BIN="php"
fi

echo "============================================================"
echo " Starting NoteBridge development server at http://localhost:8000"
echo " Press Ctrl+C in this terminal to stop the server"
echo "============================================================"

# Open default browser after 1 second
(sleep 1 && xdg-open "http://localhost:8000" >/dev/null 2>&1) &

# Launch PHP built-in web server
"$PHP_BIN" -S localhost:8000 -t "$PROJECT_DIR"
