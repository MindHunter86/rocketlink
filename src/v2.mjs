'use strict';

// eslint-disable-next-line no-undef
const PAYLOAD_DIV_TOKEN = __PAYLOAD_DIV_TOKEN__;

// eslint-disable-next-line no-undef
const COOKIE_ARGS = __COOKIE_ARGS__;

// eslint-disable-next-line no-undef
const DISABLE_RELOAD = __DISABLE_RELOAD__;

// eslint-disable-next-line no-undef
const APP_VERSION = __APP_VERSION__;

// eslint-disable-next-line no-undef
const IDB_VERSION = __IDB_VERSION__;

// stop assets download timer
if (window.__appBootOk) window.__appBootOk();

// todo : uncomment on next release
// function getRandomInt(min, max) {
//     return Math.floor(Math.random() * (max - min)) + min;
// }

// function getRandomPack() {
//     return getRandomInt(0, emotionsMatrix.contents.length);
// }

// function preloadImage(url) {
//     const img = new Image();
//     img.src = url;
// }

// function getRandomEmotion(pack, emotion) {
//     let maxidx = 0;
//     const names = [];
//     for (const file of emotionsMatrix.contents[pack].contents) {
//         if (file.type === emotion) {
//             maxidx++;
//             names.push(file);
//         }
//     }

//     const packPath = emotionsMatrix.contents[pack].name;

//     // hardcoded fallback to legacy static
//     if (names.length === 0) {
//         return `pack6/${emotion}.gif`
//     }

//     const idx = getRandomInt(0, maxidx);
//     return `${packPath}/${names[idx].name}`
// }

// function loadAssetsPack(assetsPack) {
//     const emotions = ['hello', 'error', 'done'];

//     for (const emotion of emotions) {
//         const newsrc = document.getElementById(`status-${emotion}`).src
//             .replace(/\/[^\\/]*$/, `/${getRandomEmotion(assetsPack, emotion)}`);

//         document.getElementById(`status-${emotion}`).src = newsrc;
//         preloadImage(newsrc);
//     }

//     document.getElementById("status-hello").style.display = 'block';
// }

// since type="module", script automatically uses defer for script
// so DOMContentLoaded should work
document.addEventListener('DOMContentLoaded', async () => {
    const dbName = "saeko";
    const storeNamePrefix = "challenger";
    const storeName = `${storeNamePrefix}_${APP_VERSION}`;
    const keyName = "challengeKeyPair";

    // todo : uncomment on next release
    // const assetsPack = getRandomPack();
    // loadAssetsPack(assetsPack);
    document.getElementById("status-hello").style.display = 'block';

    // --- Utils
    const toBase64url = (buf) => btoa(String.fromCharCode(...buf))
        .replace(/\+/g, '-').replace(/\//g, '_').replace(/[=]+$/, '');
    const fromBase64url = (str) => Uint8Array.from(atob(str
        .replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));

    // --- Import from JWK
    function importPrivateKey(jwk) {
        return crypto.subtle.importKey(
            "jwk",
            jwk,
            { name: "RSASSA-PKCS1-v1_5", hash: "SHA-256" },
            false,
            ["sign"]
        );
    }

    // --- IndexedDB check availability
    function hasIndexedDBSupport() {
        try {
            const idx = Boolean(indexedDB);
            return typeof indexedDB !== "undefined" && idx;
        } catch {
            return false;
        }
    }

    // --- IndexedDB open
    function openDB() {
        return new Promise((resolve, reject) => {
            if (!hasIndexedDBSupport()) reject(new Error("IndexedDB could not be opened on this device"));

            console.info(`saeko: frontend version - ${APP_VERSION}`);
            console.info(`saeko: idb version - ${IDB_VERSION}`);

            const request = indexedDB.open(dbName, IDB_VERSION);
            request.onupgradeneeded = () => {
                console.info("saeko: idb onupgradeneeded called");

                const db = request.result;
                if (!db.objectStoreNames.contains(storeName)) {
                    console.info(`saeko: created objstore in our idb : ${storeName}`);
                    db.createObjectStore(storeName);
                }

                // remove all old version
                for (const name of db.objectStoreNames) {
                    console.info(`saeko: found objstore in our idb : ${name}`);
                    if (name.startsWith(storeNamePrefix) && name !== storeName) {
                        console.info(`saeko: removed objstore from our idb : ${name}`);
                        db.deleteObjectStore(name);
                    }
                }
            };
            request.onblocked = () => reject(new Error("idb is blocked, restart your browser and try again"));
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    // --- Remove DB
    function removeDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.deleteDatabase(dbName);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    // --- Read key from DB
    function loadKey(db) {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, "readonly").objectStore(storeName);
            const req = tx.get(keyName);
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = () => reject(req.error);
        });
    }

    // --- Save key to DB
    function saveKey(db, jwk) {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, "readwrite").objectStore(storeName);
            const req = tx.put(jwk, keyName);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    }

    // --- Remove key from DB
    function removeKey(db) {
        return new Promise((resolve, reject) => {
            const tx = db.transaction(storeName, "readwrite").objectStore(storeName);
            const req = tx.delete(keyName);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    }

    // --- Generate keypair
    function generateKeyPair(len) {
        return crypto.subtle.generateKey(
            {
                name: "RSASSA-PKCS1-v1_5",
                modulusLength: len,
                publicExponent: new Uint8Array([1, 0, 1]), // 65537
                hash: "SHA-256"
            },
            true,
            ["sign", "verify"]
        );
    }

    // --- Check modulus size
    function isLengthOk(n, len) {
        const nl = fromBase64url(n).length;
        if (nl !== len / 8) {
            console.error("key's modulus doesn't meet the received requirements")
            return false
        }

        return true
    }

    function setVisualStatus(tmsg, smsg, emotion, showdet) {
        const title = document.getElementById('title');
        title.innerHTML = tmsg === "" ? title.innerHTML : tmsg;

        document.getElementById('status').innerHTML = smsg;

        document.getElementById("status-hello").style.display = "none";
        document.getElementById(`status-${emotion}`).style.display = "block";

        // errored state
        if (showdet) {
            const details = document.getElementById('error-details');
            const debuginfo = document.getElementById('debuginfo');
            const supportinfo = document.getElementById('supportinfo');

            details.style.display = "block";
            debuginfo.style.display = "block";
            debuginfo.innerHTML += ` (${new Date().toISOString()})`;
            supportinfo.style.display = "block";
        }
    }

    function hideVisualSpinner() {
        const spinner = document.getElementById('spinner');

        spinner.innerHTML = "";
        spinner.style.display = "none";
    }

    function respondWithError(err, msg) {
        const desc = !err || !err.message ? "Unexpected undefined error" : err.message;
        setVisualStatus(
            "Wow! It is an error!",
            `${msg}:<br />${desc}`,
            "error",
            true
        );
        hideVisualSpinner();
        throw new Error(desc);
    }

    function setCookie(k, v) {
        const cookieString = `${k}=${v}; Path=/${COOKIE_ARGS}`;
        document.cookie = cookieString;
    }

    // --- Payload
    let payload, length; // skipcq: JS-0119 there is no anti-pattern
    document.querySelectorAll(`[data-challenge-${PAYLOAD_DIV_TOKEN}]`).forEach((article) => {
        payload = article.getAttribute(`data-challenge-${PAYLOAD_DIV_TOKEN}`);
        if (payload === null || payload === '') respondWithError(null, `Attribute missing or empty: data-challenge-${PAYLOAD_DIV_TOKEN}`);
    });
    document.querySelectorAll(`[data-challenge-len-${PAYLOAD_DIV_TOKEN}]`).forEach((article) => {
        length = article.getAttribute(`data-challenge-len-${PAYLOAD_DIV_TOKEN}`);
        if (length === null || length === '') respondWithError(null, `Attribute missing or empty: data-challenge-len-${PAYLOAD_DIV_TOKEN}`);
        const validLengths = ["2048", "4096", "8192"];
        if (!validLengths.includes(length)) respondWithError(null, "Unexpected length in data-challenge-len");
    });

    // --- Crypto
    setVisualStatus("", "Opening database ...", "hello");
    const db = await openDB().catch((err) => {
        removeDB().catch((err2) => {
            console.error(err2, "could not drop database after open() error");
        });
        respondWithError(err, "Could not setup db for further work");
    });

    setVisualStatus("", "Loading keys ...", "hello");
    let jwk = await loadKey(db).catch((err) => {
        removeDB().catch((err2) => {
            console.error(err2, "could not drop database after loadKey() error");
        });
        respondWithError(err, "Could not load keys from DB");
    });

    let privateKey, publicParamN;  // skipcq: JS-0119 there is no anti-pattern
    if (jwk) {
        privateKey = await importPrivateKey(jwk).catch((err) => {
            respondWithError(err, "Could not export data from keys");
        });
        publicParamN = jwk.n;

        if (!isLengthOk(publicParamN, length)) {
            setVisualStatus("", "Removing old keys ...", "hello");

            removeKey(db).catch((err) => {
                console.error(err, "could not drop keys after verify() error");
            });

            jwk = null;
        }
    }

    if (!jwk) {
        setVisualStatus("", "Generating keys ...", "hello");
        const keyPair = await generateKeyPair(parseInt(length, 10)).catch((err) => {
            respondWithError(err, "browser crypto internal error");
        });

        if (!keyPair.privateKey || !keyPair.publicKey) {
            respondWithError(new Error("key pair generation error"), "Could not generate keys!");
        }

        jwk = await crypto.subtle.exportKey("jwk", keyPair.privateKey);
        await saveKey(db, jwk).catch((err) => {
            respondWithError(err, "Could not save keys in DB");
        });

        privateKey = keyPair.privateKey;
        publicParamN = jwk.n;
    }

    // --- Signing
    setVisualStatus("", "Signing payload ...", "hello");
    const signature = await crypto.subtle.sign(
        { name: "RSASSA-PKCS1-v1_5" },
        privateKey,
        fromBase64url(payload.slice(0, -3))
    ).catch((err) => {
        respondWithError(err, "Could not sign data for passing challenge");
    });

    setCookie("__SAEKOP", payload);

    const args = new URLSearchParams();
    args.append("n", publicParamN);
    args.append("s", toBase64url(new Uint8Array(signature)));

    // todo - maybe add here `Accept: json`
    setVisualStatus("", "Sending signed payload ...", "hello");
    await fetch(`/.within.website/x/cmd/saeko/identity-challenge/verify/${length}?${args}`, {
        headers: {
            "X-Requested-With": "NonXMLHttpRequest",
            "Accept": "text/javascript",
            "Cache-Control": "no-cache",
        }
    })
        .then((rsp) => {
            if (!rsp.ok) {
                if (rsp.status === 502 || rsp.status === 504) {
                    throw new Error(`Backend is currently unavailable, please try again later (HTTP:${rsp.status})`);
                }
                return rsp.json().then((err) => {
                    throw new Error(`Verification Error: ${rsp.status}: ${err.message}`);
                });
            }

            setVisualStatus(
                "ありがとう～",
                "Done! Challenge has been decrypted and accepted<br />Redirecting to AniLibria site ...",
                "done",
                false
            );
            hideVisualSpinner();

            return setTimeout(() => {
                // window.location.href = "https://aniliberty.tv";

                if (!DISABLE_RELOAD) {
                    window.location.reload(true);
                }
            }, 1000);
        })
        .catch((err) => {
            removeKey(db).catch((err2) => {
                console.error(err2, "could not drop keys after verify() error");
            });
            respondWithError(err, "We got an error on /verify HTTP request");
        })
});