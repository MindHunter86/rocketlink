import * as actions from "./actions.js";
import * as utils from "./utils.js";

// shortcut
const $ = (sel, root = document) => root.querySelector(sel);

const routeToPath = {
  home: "/",
  prices: "/prices",
  about: "/about",
  github: "/github",
  login: "/login",
  logout: "/logout",
  links: "/links",
  "ui-pricesdet": "/ui-pricesdet",
  "ui-shortenlist": "/ui-shortenlist",
  "ui-shortendtls": "/ui-shortendtls",
  "ui-cart": "/ui-cart",
  "ui-payment": "/ui-payment",
};

const routeToTpl = {
  home: "page_template-home",
  prices: "page_template-prices",
  about: "page_template-about",
  github: "page_template-github",
  login: "page_template-auth",
  logout: "page_template-home",
  links: "page_template-shrtlist",
  "ui-payment": "page_utemplate-addpayment",
  "ui-cart": "page_utemplate-cart",
  "ui-shortenlist": "page_utemplate-shrtlist",
  "ui-shortendtls": "page_utemplate-shrtdtls",
  "404": "page_template-404",
};

function showError(err) {
  console.error(err);
  alert(err?.message || "Ошибка");
}

// rendering
export async function render(route, { initial = false } = {}) {
  mount(route);
  setActiveNav(route);

  // minimal hooks
  try {
    if (initial === true) await actions.page_initial_loading();

    if (route === "login") await actions.page_login_login();
    else if (route === 'logout') await actions.page_login_logout();
    else if (route === 'links') await actions.page_shrtlist_list();
    else if (route === "profile") await loadMe();
  } catch (e) {
    showError(e);
  }
}

// history API navigation
export async function navigate(route, { replace = false } = {}) {
  const path = routeToPath[route] || "/404";
  const state = { route };

  if (replace) history.replaceState(state, "", path);
  else history.pushState(state, "", path);

  render(route, { initial: replace });
}

export function mount(route) {
  const app = $("#app");
  app.textContent = "";

  const tplId = routeToTpl[route] || routeToTpl["404"];
  const tpl = $("#" + tplId);
  app.appendChild(tpl.content.cloneNode(true));
}

export function setActiveNav(route) {
  document.querySelectorAll("nav a[data-route]").forEach(a => {
    a.classList.toggle("active", a.dataset.route === route);
  });
}

export function navSwitchLoggedView(islogged) {
  document.querySelectorAll("nav a[data-islogged]").forEach(a => {
    if (a.dataset.islogged === islogged) a.classList.remove('disabled');
    else a.classList.add('disabled');
  });
}

export function shortenListPrint(links) {
  if (!links) return;

  const frag = document.createDocumentFragment();
  links.forEach(link => frag.appendChild(makeShortenListElem(link)));
  $('#page_shrtlist-list').replaceChildren(frag);
}

function makeShortenListElem(link) {
  const tpl = $("#tpl_shrtlist-card");
  const node = tpl.content.firstElementChild.cloneNode(true);

  console.log(node);
  if (!node) return;

  node.querySelector('[data-bind="shortenid"]').textContent = 'trm.sh/' + link.shortenid;
  node.querySelector('[data-bind="shortendate"]').textContent = utils.formatShortenDate(link.created_at);
  node.querySelector('[data-bind="shortendst"]').href = link.destination;
  node.querySelector('[data-bind="shortendst"]').textContent = utils.shortHost(link.destination);

  return node;
}