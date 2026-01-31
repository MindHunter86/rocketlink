import * as views from './views.js';

import * as api from "./api.js";
import * as session from "./session.js";

// shortcut
const $ = (sel, root = document) => root.querySelector(sel);

// some root routing
const pathToRoute = {
    "/": "home",
    "/prices": "prices",
    "/about": "about",
    "/github": "github",
    "/login": "login",
    "/logout": "logout",
    "/links": "links",
    "/ui-pricesdet": "ui-pricesdet",
    "/ui-shortenlist": "ui-shortenlist",
    "/ui-shortendtls": "ui-shortendtls",
    "/ui-cart": "ui-cart",
    "/ui-payment": "ui-payment",
};

const routes = [
    { re: /^\/$/, view: "home" },
    { re: /^\/about$/, view: "about" },

    { re: /^\/links$/, view: "links" },
    { re: /^\/links\/(?<id>[a-zA-Z0-9-]+)$/, view: "links_detailed" },
];

// click hijack for SPA routing
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


// async function loadPosts() {
//     const ul = $("#posts");
//     if (!ul) return;

//     ul.textContent = "Загрузка...";
//     const posts = await api("/posts"); // ожидаем JSON-массив
//     ul.textContent = "";

//     for (const p of posts) {
//         const li = document.createElement("li");
//         li.textContent = p.title ?? String(p);
//         ul.appendChild(li);
//     }
// }

// async function loadMe() {
//     const box = $("#userBox");
//     if (!box) return;

//     box.textContent = "Загрузка...";
//     const me = await api("/me");
//     box.textContent = JSON.stringify(me, null, 2);
// }

