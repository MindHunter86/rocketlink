import * as views from './views.js';

import * as api from "./api.js";
import * as session from "./session.js";

// shortcut
const $ = (sel, root = document) => root.querySelector(sel);

// click hijack for SPA routing
// !! BUG - Enter key is escape from event
document.addEventListener("click", (e) => {
    // nav a - only click
    const link = e.target.closest("a[data-route]");
    if (!link) return;
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

    // ???
    const url = new URL(link.href, location.origin);
    if (url.origin !== location.origin) return;

    e.preventDefault();
    views.navigate(url.pathname);

    // button's actions
    // const btn = e.target.closest("[data-action]");
    // if (!btn) return;

    // const actions = { loadPosts, loadMe };
    // const fn = actions[btn.dataset.action];
    // if (fn) fn().catch(showError);
});

// history + route
window.addEventListener("popstate", (e) => {
    views.render(e.state || matchRoute(location.pathname));
});

// load session details
await session.InitialApiLoad();

// initial state
views.navigate(location.pathname, true);
