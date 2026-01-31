import { navSwitchLoggedView, navigate } from "./views.js";
import * as views from './views.js';
import { sessiondata, CleanAndFetchSession } from "./session.js";
import { switchSceneForForms } from "./utils.js";
import { api } from "./api.js";

// shortcut
const $ = (sel, root = document) => root.querySelector(sel);

export async function page_initial_loading() {
    console.log("actions.page - initial page loading");

    if (!sessiondata) return;

    if (sessiondata["is_logged_in"] === true) navSwitchLoggedView("true");
    else navSwitchLoggedView("false");
}

// TODO : error notification
export async function page_login_login() {
    console.log("actions.page - login page loading");

    const loginform = $("#form_auth-common");
    const passwordform = $("#form_auth-signin");
    const signupform = $("#form_auth-signup");

    if (!loginform || !passwordform || !signupform) return;

    $('#input_common-email').focus();
    $('#input_common-email').select();

    loginform.addEventListener('submit', async (e) => {
        e.preventDefault();

        let data = new FormData(e.target);
        sessiondata["login_username"] = data.get("username");

        api('/accounts/users/auth/check', {
            method: 'POST',
            body: 'username=' + data.get("username"),
            credentials: 'include',
        })
            .then((response) => {
                if (!response || response.status !== "ok") console.log("API respond with non-parsable object");

                switchSceneForForms(loginform, passwordform);
                $('#input_signin-password').focus();
                $('#input_signin-password').select();
            })
            .catch(() => {
                switchSceneForForms(loginform, signupform);
                $('#input_signup-email').value = sessiondata['login_username'];
                $('#input_signup-password').focus();
                $('#input_signup-password').select();
            });

    });

    passwordform.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!sessiondata["login_username"]) {
            navigate("/");
            return;
        }

        let data = new FormData(e.target);
        api('/accounts/users/auth/login', {
            method: 'POST',
            body: `username=${sessiondata["login_username"]}&password=${data.get("password")}`,
            credentials: 'include',
        })
            .then((response) => {
                if (!response || response.status !== "ok") console.log("API respond with non-parsable object");

                navSwitchLoggedView("true");
                navigate("/");
            })
            .catch(() => {
                $('#input_signin-password').value = "";
                switchSceneForForms(passwordform, loginform);
                $('#input_common-email').value = sessiondata['login_username'];
                $('#input_common-email').focus();
                $('#input_common-email').select();
            });
    });

    signupform.addEventListener('submit', async (e) => {
        e.preventDefault();

        let data = new FormData(e.target);
        api('/accounts/users/register', {
            method: 'POST',
            body: `username=${data.get("username")}&password=${data.get("password")}&purpose=${data.get("purpose")}&eula=${data.get("eula") === "true" ? "true" : "false"}`,
            credentials: 'include',
        })
            .then((response) => {
                if (!response || response.status !== "ok") console.log("API respond with non-parsable object");
                switchSceneForForms(signupform, loginform);
                $('#input_common-email').value = sessiondata['login_username'];
                $('#input_common-email').focus();
                $('#input_common-email').select();
            })
            .catch(() => {
                console.log("API request failure!");
                navigate("/");
            });
    });
}

export async function page_login_logout() {
    console.log("actions.page - logout page loading");

    const response = await api('/accounts/users/auth/logout', {
        method: 'POST',
        credentials: 'include',
    });
    if (!response || response.status !== "ok") console.log("API respond with non-parsable object");

    navSwitchLoggedView("false");
    navigate("/");
}

export async function page_shrtlist_list() {
    console.log("actions.page - shrtlist list page loading");

    api('/accounts/users/links', {
        credentials: 'include',
    })
        .then((response) => {
            if (!response || response.status !== "ok") console.log("API respond with non-parsable object");
            views.shortenListPrint(response.data);
        })
        .catch((e) => {
            console.log(e);
            console.log("API request failure!");
            navigate("/");
        });
}