// ioFetch will be loaded dynamically to ensure we use the same versioned URL
// and avoid loading the module twice. Use `window.admProInstallerAdminUrl` and
// `window.admProInstallerVersion` which are injected by the template.
import { ioFetch } from "admpro/ioFetch";

(function () {
    const root = document.querySelector(".admpro-installer");
    if (!root) {
        return;
    }
    const state = window.admProInstallerState || {};

    const ui = {
        status: document.createElement("div"),
        actions: document.createElement("div"),
        meta: root.querySelector(".meta") || document.createElement("div"),
        spinner: document.createElement("div"),
    };
    ui.status.className = "admpro-status my-3";
    ui.actions.className = "admpro-actions my-3";
    ui.spinner.innerHTML =
        '<div class="text-center my-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';

    // Clear old static buttons
    root.querySelectorAll("a.btn").forEach((el) => el.remove());
    const infoBlocks = root.querySelectorAll(".info");

    root.appendChild(ui.status);
    root.appendChild(ui.actions);

    function render(s) {
        // remove existing info blocks for dynamic messages
        infoBlocks.forEach((el) => el.remove());
        ui.status.innerHTML = "";
        // Decide if the actions area will already render the success message
        function actionsWillRenderSuccess(state) {
            if (!state) return false;
            if (!state.success) return false;
            if (state.installed) return true;
            if (
                state.phase === "ready-download" ||
                state.phase === "ready-update" ||
                state.phase === "downloading" ||
                state.phase === "updating"
            )
                return true;
            return false;
        }

        if (s.error) {
            ui.status.innerHTML = `<div class="alert alert-danger">${escapeHtmlWithNewlines(
                s.error
            )}</div>`;
        } else if (s.success && !actionsWillRenderSuccess(s)) {
            // Only render top-level success when actions area won't duplicate it
            ui.status.innerHTML = `<div class="alert alert-success">${escapeHtmlWithNewlines(
                s.success
            )}</div>`;
        } else {
            ui.status.innerHTML = "";
        }
        ui.actions.innerHTML = "";

        if (!s.extracted && !s.installed && s.phase === "ready-download") {
            const spinner = ui.spinner.cloneNode(true);
            ui.actions.appendChild(spinner);
            if (s.success) {
                const msg = document.createElement("div");
                msg.className = "mt-2";
                msg.innerHTML = escapeHtml(s.success);
                ui.actions.appendChild(msg);
            }
        } else if (
            s.extracted &&
            !s.installed &&
            (s.phase === "ready-update" || s.phase === "downloading")
        ) {
            const spinner = ui.spinner.cloneNode(true);
            ui.actions.appendChild(spinner);
            // show a server-provided success/info message if available (e.g., 'Files extracted')
            if (s.success) {
                const msg = document.createElement("div");
                msg.className = "mt-2";
                msg.innerHTML = escapeHtml(s.success);
                ui.actions.appendChild(msg);
            }
        } else if (s.phase === "updating") {
            const spinner = ui.spinner.cloneNode(true);
            ui.actions.appendChild(spinner);
            if (s.success) {
                const msg = document.createElement("div");
                msg.className = "mt-2";
                msg.innerHTML = escapeHtml(s.success);
                ui.actions.appendChild(msg);
            }
        } else if (s.installed) {
            // Prefer server-provided success message when available, fallback to hardcoded text
            const installedMsg = s.success || "Plugin installiert / Installed.";
            ui.actions.innerHTML = `<div class="alert alert-success mb-2">${escapeHtml(
                installedMsg
            )}</div>`;
            const admUrl = s.admProBackendUrl;
            // If installation succeeded, reload once after a short delay so the admin sidebar updates
            // perform redirect after a short delay; attempt self-uninstall first
            setTimeout(async () => {
                try {
                    // Prefer redirecting to the plugin backend URL so the admin sidebar shows the installed plugin
                    if (admUrl) {
                        try {
                            // Safely append installed=true to existing URL, preserving hash and existing query
                            const url = new URL(admUrl, window.location.origin);
                            // If installed param not present, set it
                            if (!url.searchParams.has("installed")) {
                                url.searchParams.set("installed", "true");
                            }
                            try {
                                await ioFetch(
                                    "admProInstaller",
                                    "apiUninstall"
                                );
                            } catch (e) {
                                console.warn(
                                    "Self-uninstall failed or returned error",
                                    e
                                );
                            }
                            window.location.href = url.toString();
                        } catch (e) {
                            // If URL constructor fails (relative URL edge-case), fall back to simple append
                            const sep = admUrl.includes("?") ? "&" : "?";
                            try {
                                await ioFetch(
                                    "admProInstaller",
                                    "apiUninstall"
                                );
                            } catch (err) {
                                console.warn(
                                    "Self-uninstall failed or returned error",
                                    err
                                );
                            }
                            window.location.href =
                                admUrl + sep + "installed=true";
                        }
                    } else {
                        // No backend URL available; don't reload the current page — do nothing.
                    }
                } catch (err) {
                    console.error(
                        "Failed to redirect/reload page after install",
                        err
                    );
                }
            }, 1200);
        }
    }

    function escapeHtml(str) {
        if (str == null) return "";
        return String(str).replace(
            /[&<>"']/g,
            (c) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                }[c])
        );
    }

    function escapeHtmlWithNewlines(str) {
        const escaped = escapeHtml(str);
        // convert LF and CRLF to <br>
        return escaped.replace(/\r?\n/g, "<br>");
    }

    async function progress(s) {
        if (s.error || s.installed) return;
        if (!s.extracted && s.phase === "ready-download") {
            try {
                const next = await ioFetch("admProInstaller", "apiDownload");
                render(next);
                await progress(next);
            } catch (e) {
                console.error(e);
                render({ error: getErrorMessage(e, "Download Fehler") });
            }
        } else if (s.extracted && !s.installed && s.phase === "ready-update") {
            try {
                const next = await ioFetch("admProInstaller", "apiUpdate");
                render(next);
                await progress(next);
            } catch (e) {
                console.error(e);
                render({ error: getErrorMessage(e, "Installations Fehler") });
            }
        }

        // Helper to map error codes to messages if present in response
        function getErrorMessage(error, fallback) {
            // If server rejected with an object like { error: 'msg' }
            if (error && typeof error === "object") {
                if (error.error) return error.error;
                if (error.message) return error.message;
                return fallback;
            }
            // If an Error instance was thrown with a JSON-stringified message
            if (error instanceof Error) {
                // try to parse JSON inside error.message
                try {
                    const parsed = JSON.parse(error.message);
                    if (parsed && parsed.error) return parsed.error;
                } catch (e) {
                    // not JSON
                }
                // fallback to the error's text
                return error.message || fallback;
            }
            if (typeof error === "string") {
                return error;
            }
            return fallback;
        }
    }

    render(state);
    // Kick off automatic flow
    (async () => {
        try {
            // refresh state first to ensure server-side markers changed after page cache etc.
            const fresh = await ioFetch("admProInstaller", "apiState");
            render(fresh);
            await progress(fresh);
        } catch (e) {
            console.error(e);
            progress(state); // fallback
        }
    })();
})();
