## Automated build and cPanel deployment

This project includes a GitHub Actions workflow that builds frontend assets and publishes the built `public/build` into a dedicated `build` branch on pushes to `main`.

How it works

- Push changes to `main` on GitHub.
- GitHub Actions runs `npm ci` and `npm run build`.
- If `public/build` changed, the action updates the `build` branch with the new build (commit message contains `[skip ci]`).

cPanel usage

1. In cPanel, use the Git Version Control or your preferred method to clone this repository.
2. Pull from GitHub as usual. You have two options on cPanel:

- Pull the `main` branch (code only) and run build locally on your machine before deploying.
- Or pull the `build` branch to get compiled assets directly: `git pull origin build`.
3. If public/storage symlink is not available on the host, use the app's fallback routes:
   - `/storage/status` — returns JSON about whether `public/storage` exists and is a symlink.
   - `/storage/image/{path}` — serves files from `storage/app/public/{path}`.

Notes & alternatives

- The workflow now publishes built assets to a separate branch: `build`.
- Make sure your GitHub repo has Actions enabled and `GITHUB_TOKEN` permission to write (workflow file already requests `contents: write`).
- If your hosting blocks building on the server (no Node), this approach avoids needing to run `npm run build` on cPanel.

Security

- The workflow commits using the default `GITHUB_TOKEN`. Pushing build artifacts to the repository is convenient but consider whether you want built files tracked in Git history.

If you want, I can also:
- Update the workflow to push to a separate `build` branch instead of modifying `main`.
- Add a small script on the cPanel server to automatically `git pull` periodically or on webhook.
