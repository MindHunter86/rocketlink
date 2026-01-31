import { api } from "./api.js";

// session object
export let sessiondata = [];

export async function InitialApiLoad() {
    const sess = await api("/debug/session", {
        credentials: 'include',
    });
    if (!sess) return;

    sessiondata["is_anonymous"] = sess.account.is_anonymous;
    sessiondata["is_logged_in"] = sess.account.is_logged_in;
}
export async function CleanAndFetchSession() {
    sessiondata = [];
    InitialApiLoad();
}