window.onload = () => {
    let links = document.querySelectorAll("[data-delete]")

    for (link of links) {
        link.addEventListener("click", function (event) {
            event.preventDefault()

            if (confirm("Êtes vous sur de vouloir supprimer ?")) {
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    }
                }).then(
                    response => response.json()
                ).then(data => {
                    if (data.success) {
                        let quantities = document.querySelectorAll("[data-quantity]")
                        for (quantity of quantities) {
                            if (quantity.innerHTML > 1) 
                                quantity.innerHTML -= 1; 
                            else {
                                this.parentElement.parentElement.parentElement.parentElement.remove()
                                window.alert("Votre panier est vide !")
                            }
                                
                        }
                        let totals = document.querySelectorAll("[data-total]")
                        for (total of totals) {
                            if (total.innerHTML) {
                                let priceProduct = document.querySelector("[data-price]")
                                total.innerHTML -= parseInt(priceProduct.innerHTML)
                            }
                        }
                    }
                    else
                        window.alert(data.error)
                }).catch(e => window.alert(e))
            }
        })
    }
}
