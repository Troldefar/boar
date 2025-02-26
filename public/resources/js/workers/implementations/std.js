onmessage = async(e) => {
    const fd = new FormData();
    for (let key in e.data) {
        if (e.data[key] instanceof Array) {
            row = 0;
            for (let iteration in e.data[key]) {
                fd.append(`${key.replace('[]', '')}${row}`, e.data[key][iteration]);
                row++;
            }
        } else {
            fd.append(key, e.data[key]);
        }
    };

    try {
        const response = await fetch(e.data.url ?? '/file', {method: "POST", body: fd});
        const jsonResponse = await response.json();
        postMessage(jsonResponse.responseJSON ?? '');
    } catch {
        postMessage('Error'); 
    }
}