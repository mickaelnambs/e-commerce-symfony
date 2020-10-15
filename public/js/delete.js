window.onload = () => {
    let links = document.querySelectorAll("[data-delete]")

    for (link of links) {
        link.addEventListener("click", function (event) {
            event.preventDefault()

            if (confirm("Voulez-vous le supprimer ?")) {
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    }
                }).then(
                    response => response.json()
                ).then(data => {
                    if (data.success)
                        this.parentElement.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e => alert(e))
            }
        })
    }
}