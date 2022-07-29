CREATE TABLE IF NOT EXISTS boulangerie.order
(
    cookie VARCHAR(255) NOT NULL,
    statut VARCHAR(30) NOT NULL,
    tva DOUBLE(4,2) NOT NULL,
    UNIQUE(cookie),
    PRIMARY KEY(cookie)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS boulangerie.order_row
(
    id_order VARCHAR(255),
    quantity INT,
    ref VARCHAR(100) NOT NULL,
    price DOUBLE(5, 2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    CONSTRAINT FK_id_order FOREIGN KEY (id_order) REFERENCES boulangerie.order(cookie) ON DELETE CASCADE,
    PRIMARY KEY(ref, id_order)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS boulangerie.facture
(
    id VARCHAR(255) NOT NULL,
    numero INT NOT NULL AUTO_INCREMENT,

    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,

    created_at DATETIME NOT NULL,

    id_stripe VARCHAR(255) NOT NULL,

    statut VARCHAR(30) NOT NULL,
    tva DOUBLE(4,2) NOT NULL,

    tva_amount DOUBLE(4,2) NOT NULL,
    subtotal DOUBLE(4,2) NOT NULL,
    total DOUBLE(4,2) NOT NULL,
    UNIQUE(id),
    UNIQUE(numero),
    PRIMARY KEY(id)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS boulangerie.facture_row
(
    id_facture VARCHAR(255),
    quantity INT,
    ref VARCHAR(100) NOT NULL,
    price DOUBLE(5, 2) NOT NULL,
    name VARCHAR(255) NOT NULL,
    CONSTRAINT FK_id_facture FOREIGN KEY (id_facture) REFERENCES boulangerie.facture(id) ON DELETE CASCADE,
    PRIMARY KEY(ref, id_facture)
) ENGINE=INNODB;