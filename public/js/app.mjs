import { mount, navigate, render, setActiveNav } from "./views.js";

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

function getRouteFromLocation() {
    const path = location.pathname.replace(/\/+$/, "") || "/";
    return pathToRoute[path] || "404";
}

// history + route
window.addEventListener("popstate", (e) => {
    const route = e.state?.route || getRouteFromLocation();
    render(route);
});

// click hijack for SPA routing
document.addEventListener("click", (e) => {
    // nav a
    const link = e.target.closest("a[data-route]");
    if (link) {
        // only click
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        e.preventDefault();
        navigate(link.dataset.route);
        return;
    }

    // button's actions
    const btn = e.target.closest("[data-action]");
    if (!btn) return;

    const actions = { loadPosts, loadMe };
    const fn = actions[btn.dataset.action];
    if (fn) fn().catch(showError);
});


// 6) Actions
async function loadPosts() {
    const ul = $("#posts");
    if (!ul) return;

    ul.textContent = "Загрузка...";
    const posts = await api("/posts"); // ожидаем JSON-массив
    ul.textContent = "";

    for (const p of posts) {
        const li = document.createElement("li");
        li.textContent = p.title ?? String(p);
        ul.appendChild(li);
    }
}

async function loadMe() {
    const box = $("#userBox");
    if (!box) return;

    box.textContent = "Загрузка...";
    const me = await api("/me");
    box.textContent = JSON.stringify(me, null, 2);
}


// load session details
await session.InitialApiLoad();

// initial state
const initialRoute = getRouteFromLocation();
navigate(initialRoute, { replace: true });