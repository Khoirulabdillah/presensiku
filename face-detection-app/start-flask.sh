#!/bin/bash

# Script untuk menjalankan Flask Face Detection Server
# Usage: ./start-flask.sh [port]

# Set default port
PORT=${1:-5001}

# Set environment variables
export FLASK_SERVER_PORT=$PORT

# Path ke virtual environment
VENV_PATH="/Users/inandraashafardhana/code/website/presensiku/.venv"

# Path ke app.py
APP_PATH="/Users/inandraashafardhana/code/website/presensiku/face-detection-app/src"

echo "🚀 Starting Flask Face Detection Server..."
echo "📍 Port: $PORT"
echo "📁 App Path: $APP_PATH"
echo ""

# Check if port is already in use
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Port $PORT is already in use!"
    echo "Processes using port $PORT:"
    lsof -i :$PORT
    echo ""
    read -p "Do you want to kill the process and restart? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Killing process on port $PORT..."
        lsof -ti :$PORT | xargs kill -9
        sleep 2
    else
        echo "Exiting..."
        exit 1
    fi
fi

# Change to app directory
cd "$APP_PATH" || exit 1

# Start Flask server
echo "Starting Flask server..."
"$VENV_PATH/bin/python" app.py

# If we get here, the server has stopped
echo ""
echo "❌ Flask server stopped"
