// TODO : signal - to delete, draft for request abort feature
export async function api(path, { method = "GET", body, credentials } = {}) {
    const res = await fetch(`http://localhost:8080/api/v1${path}`, {
        method,
        headers: body ? { "Content-Type": "application/x-www-form-urlencoded" } : undefined,
        body: body ? body : undefined,
        credentials: credentials ? credentials : undefined,

        // headers: body ? { "Content-Type": "application/json" } : undefined,
        // body: body ? JSON.stringify(body) : undefined,
    });

    if (!res.ok) {
        const text = await res.text().catch(() => "");
        throw new Error(`API ${res.status}: ${text || res.statusText}`);
    }

    const ct = res.headers.get("content-type") || "";
    return ct.includes("application/json") ? res.json() : res.text();
}
