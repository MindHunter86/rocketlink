'use strict';

document.addEventListener('change', async (event) => {
    const target = event.target;

    // only input-s
    if (!target.matches('input')) return;

    // only checkboxes and text-s
    if (target.type !== "checkbox" && target.type !== "text") return;

    // remove disabled
    if (target.disabled) return;

    // ignore empty values
    if (target.type !== "checkbox" && !target.value) return;

    const args = new URLSearchParams();
    args.append("key", target.name);
    args.append("value", target.type === "checkbox" ? target.checked : target.value);

    await fetch(`/.within.website/x/cmd/saeko/internal/api/config?${args}`, {
        method: "PATCH",
        mode: "cors",
        headers: {
            "X-Requested-With": "NonXMLHttpRequest",
            "Accept": "text/javascript",
            "Cache-Control": "no-cache",
            "Origin": "localhost",
        }
    })
        .then((rsp) => {
            if (!rsp.ok) {
                if (rsp.status === 502 || rsp.status === 504) {
                    throw new Error(`Backend is currently unavailable, please try again later (HTTP:${rsp.status})`);
                }
                return rsp.json().then((err) => {
                    throw new Error(`Patch Error: ${rsp.status}: ${err.message}`);
                });
            }

            document.getElementById('status').innerHTML = `Request completed, changes were applied!<br>(K: ${target.name}; V: ${args.get('value')})`
            return null;
        })
        .catch((err) => {
            document.getElementById('status').innerHTML = `Could not execute request: ${err}`;
            throw new Error(`/config HTTP request error - ${err}`);
        });
}, true);