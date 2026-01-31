'use strict';

// const canWAAPI = typeof Element !== "undefined" && typeof Element.prototype.animate === "function";
function hasWAAPI(el) {
    return !!el && typeof el.animate === "function";
}

export async function switchSceneForForms(prevel, currel) {
    if (hasWAAPI(prevel) && hasWAAPI(currel)) {
        const elanim = prevel.animate(
            [{ opacity: 1, transform: "translateY(0)" }, { opacity: 0, transform: "translateY(8px)" }],
            { duration: 150, easing: "ease-in", fill: "forwards" }
        );
        elanim.onfinish = () => {
            prevel.classList.toggle('is-visible');
            currel.classList.toggle('is-visible');
            currel.animate(
                [{ opacity: 0, transform: 'translateY(8px)' }, { opacity: 1, transform: 'translateY(0)' }],
                { duration: 150, easing: 'ease-out', fill: 'forwards' }
            );
        };

        return;
    }

    // ? TODO: rework fallback method for old devices
    // TODO: test fallback method
    console.log('no WAAPI support!');

    prevel.classList.toggle('is-visible');
    currel.classList.toggle('is-visible');
    return;
}

// function switchSceneForSections(prevel, currel) {
//     if (typeof prevel === "undefined" || typeof currel === "undefined") return false;
//     if (prevel === null || currel === null) return false;

//     if (hasWAAPI(prevel) && hasWAAPI(currel)) {
//         const elanim = prevel.animate(
//             [{ opacity: 1 }, { opacity: 0 }],
//             { duration: 150, easing: "ease-in", fill: "forwards" }
//         );
//         elanim.onfinish = () => {
//             prevel.classList.toggle('is-visible');
//             currel.classList.toggle('is-visible');
//             currel.animate(
//                 [{ opacity: 0 }, { opacity: 1 }],
//                 { duration: 150, easing: 'ease-out', fill: 'forwards' }
//             );
//         };

//         return true;
//     }

//     // ? TODO: rework fallback method for old devices
//     // TODO: test fallback method
//     console.log('no WAAPI support!');

//     prevel.classList.toggle('is-visible');
//     currel.classList.toggle('is-visible');
//     return true;
// }

export function formatShortenDate(payload) {
    const d = new Date(payload)
    if (!d) return;

    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yyyy = d.getFullYear();
    return `${dd}.${mm}.${yyyy}`;
}

export function shortHost(url) {
    return url.replace(/^https?:\/\//, "").slice(0, 15);
}