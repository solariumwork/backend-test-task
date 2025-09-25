#!/bin/bash
set -e

HOOKS_DIR=".githooks"
GIT_HOOKS_DIR=".git/hooks"

if [ ! -d "$GIT_HOOKS_DIR" ]; then
    echo "Error: .git/hooks directory not found. Are you in the git repo?"
    exit 1
fi

for hook in "$HOOKS_DIR"/*; do
    hook_name=$(basename "$hook")
    dest="$GIT_HOOKS_DIR/$hook_name"

    cp -f "$hook" "$dest"

    chmod +x "$dest"

    echo "Installed $hook_name hook."
done

echo "All hooks installed successfully."