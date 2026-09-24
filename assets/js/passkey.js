function arrayBufferToBase64(b) {
    let u = new Uint8Array(b)
    let s = ""

    for (let i = 0; i < u.byteLength; i++) {
        s += String.fromCharCode(u[i])
    }

    return btoa(s)
}

function base64ToArrayBuffer(o) {
    let pre = "=?BINARY?B?"
    let suf = "?="

    for (let k in o) {
        if (typeof o[k] !== "string") {
            base64ToArrayBuffer(o[k])
            continue
        }

        let s = o[k]

        if (s.substring(0, pre.length) == pre && s.substring(s.length - suf.length) == suf) {
            let b = window.atob(s.substring(pre.length, s.length - suf.length))
            let u = new Uint8Array(b.length)

            for (let i = 0; i < b.length; i++) {
                u[i] = b.charCodeAt(i)
            }

            o[k] = u.buffer
        }
    }
}

function fetcher(url, data = {}) {
    let form = new FormData()

    for (let [k,v] of Object.entries(data)) {
        form.append(k,v)
    }
    
    return fetch(url, { method: "POST", body: form })
        .then(res => res.json())
        .catch(console.error)
}
