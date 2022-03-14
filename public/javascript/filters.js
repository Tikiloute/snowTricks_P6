window.onload = ()  => {

    const filterFrom = document.querySelector("#filters");

    const select = document.querySelector("#category");

    select.addEventListener("change", () => {

        const form = new FormData(filterFrom);

        // create queryString
        const params = new URLSearchParams();
        
        form.forEach((value, key) => {
            params.append(key, value);
        })

        const url = new URL(window.location.href);

        fetch(url.pathname + "?" + params.toString() + "&ajax=1", {
            headers: {
                "X-Requested-With" : "XMLHttpRequest"
            }
        }).then(
            response => response.json()
        ).then(data => {
            const contents = document.querySelector("#content");
            contents.innerHTML = data.content;

            //on met à jour l'url pour la pagination
            history.pushState({}, null, url.pathname + "?" + params.toString());
        })
        .catch(e => alert(e));
    })

    
}