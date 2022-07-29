class PanierAjax {
    static async load() {
        return await fetch("/panier/full", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "max-age=0",
            },
        })
            .then((res) => res.json())
            .then((res) => {
                return res;
            });
    }

    static async clearAll() {
        return await fetch("/panier/clear", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "max-age=0",
            },
        })
            .then((res) => res.json())
            .then((res) => {
                return res;
            });
    }

    static async clearProduct($ref) {
        return await fetch("/panier/clearProduct", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                ref: $ref,
                action: "clearProduct",
            }),
        }).then((res) => res.json());
    }

    static async addToPanier($ref) {
        return await fetch("/panier/add", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                ref: $ref,
                action: "add",
            }),
        }).then((res) => res.json());
    }

    static async decreaseQuantity($ref) {
        return await fetch("/panier/decrease", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                ref: $ref,
                action: "decrease",
            }),
        })
            .then((res) => res.json())
            .then((res) => {
                return res;
            });
    }

    static async setProductQuantity($ref, $quantity) {
        return await fetch("/panier/quantity", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                ref: $ref,
                action: "setQuantity",
                quantity: $quantity,
            }),
        })
            .then((res) => res.json())
            .then((res) => {
                return res;
            });
    }
}
