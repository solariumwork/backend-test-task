#!/bin/bash
set -e

HOOKS_DIR=".githooks"
GIT_HOOKS_DIR=".git/hooks"

if [ ! -d "$GIT_HOOKS_DIR" ]; then
    echo "❌ Error: .git/hooks directory not found. Are you in the git repo?"
    exit 1
fi

for hook in "$HOOKS_DIR"/*; do
    hook_name=$(basename "$hook")
    dest="$GIT_HOOKS_DIR/$hook_name"

    if [ -f "$dest" ]; then
        rm -f "$dest"
        echo "🗑️  Removed $hook_name hook."
    else
        echo "ℹ️  $hook_name hook not found in $GIT_HOOKS_DIR. Skipping."
    fi
done

echo "✅ All hooks removed successfully."
