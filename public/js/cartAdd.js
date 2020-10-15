window.onload = () => {
    let links = document.querySelectorAll("[add-to-cart]");

    for (link of links) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            if (confirm("Ajouter dans le panier ?")) {
                fetch(this.getAttribute("href"), {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    }
                }).then(
                    respose => respose.json()
                ).then(data => {
                    if (data.success)
                        console.log(data.success)
                    else 
                        alert(data.error)
                }).catch(e => alert(e))
            }
        })
    }
}