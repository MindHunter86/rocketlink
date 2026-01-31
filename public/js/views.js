import * as actions from "./actions.js";
import * as utils from "./utils.js";

// shortcut
const $ = (sel, root = document) => root.querySelector(sel);

const routeToTpl = {
  home: "page_template-home",
  prices: "page_template-prices",
  about: "page_template-about",
  github: "page_template-github",
  login: "page_template-auth",
  logout: "page_template-home",
  links: "page_template-shrtlist",
  links_detailed: "page_template-shrtdtls",
  "ui-payment": "page_utemplate-addpayment",
  "ui-cart": "page_utemplate-cart",
  "ui-shortenlist": "page_utemplate-shrtlist",
  "ui-shortendtls": "page_utemplate-shrtdtls",

  "404": "page_template-404",
};

const routes = [
  { re: /^\/$/, view: "home" },
  { re: /^\/about$/, view: "about" },
  { re: /^\/github$/, view: "github" },
  { re: /^\/login$/, view: "login" },
  { re: /^\/logout$/, view: "logout" },
  // { re: /^\/ui-shortenlist$/, view: "ui-shortenlist" },
  // { re: /^\/ui-shortendtls$/, view: "ui-shortendtls" },
  // { re: /^\/ui-cart$/, view: "ui-cart" },
  // { re: /^\/ui-payment$/, view: "page_utemplate-addpayment" },

  { re: /^\/links$/, view: "links" },
  { re: /^\/links\/(?<id>[a-zA-Z0-9-]+)$/, view: "links_detailed" },
];

function showError(err) {
  console.error(err);
  alert(err?.message || "Ошибка");
}

// rendering
export async function render(route, initial = false) {
  console.log(route);
  mount(route.view);
  setActiveNav(route.view);

  // minimal hooks
  try {
    // document.querySelector("#productId").textContent = params.id || "—";
    if (initial === true) await actions.page_initial_loading();

    if (route.view === "login") await actions.page_login_login();
    else if (route.view === 'logout') await actions.page_login_logout();
    else if (route.view === 'links') await actions.page_shrtlist_list();
    else if (route.view === 'links_detailed') await actions.page_shrtdtls_details(route.params.id);
    else if (route.view === "profile") await loadMe();
  } catch (e) {
    showError(e);
  }
}

// history API navigation
function matchRoute(pathname) {
  const path = pathname.length > 1 ? pathname.replace(/\/$/, "") : pathname;

  for (const r of routes) {
    const m = path.match(r.re);
    if (m) {
      console.log(m);
      return { view: r.view, params: m.groups || {} };
    }
  }
  console.log(pathname);

  return { view: "404", params: {} };
}

export async function navigate(route, replace = false) {
  const match = matchRoute(route);
  console.log(match);
  const fn = replace ? history.replaceState : history.pushState;

  fn.call(history, match, "", route);
  render(match, replace);
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
  if (!node) return;

  node.querySelector('[data-bind="shortenid"]').textContent = 'trm.sh/' + link.shortenid;
  node.querySelector('[data-bind="shortenid"]').href = '/links/' + link.shortenid;
  node.querySelector('[data-bind="shortendate"]').textContent = utils.formatShortenDate(link.created_at);
  node.querySelector('[data-bind="shortendst"]').href = link.destination;
  node.querySelector('[data-bind="shortendst"]').textContent = utils.shortHost(link.destination);

  return node;
}

export function fillShortenDetails(details) {
  const node = $("#page_shrtdtls-details");
  if (!node) return;

  node.querySelector('[data-bind="shortenid"]').textContent = details.shortenid;
  node.querySelector('[data-bind="destination"]').href = details.destination;
  node.querySelector('[data-bind="destination"]').textContent = utils.shortHost(details.destination);
  node.querySelector('[data-bind="created_at"]').textContent = utils.formatShortenDate(details.created_at);
  node.querySelector('[data-bind="updated_at"]').textContent = utils.formatShortenDate(details.created_at);
}