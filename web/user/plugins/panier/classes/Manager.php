<?php

namespace Grav\Plugin\Panier;

use DateTime;
use PDO;
use Grav\Plugin\Panier\OrderRow;
use Throwable;

class Manager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function initTable():void
    {
        $content = file_get_contents(__DIR__."/../sql/tables.sql");
        $query = $this->database->getConnection()->prepare($content);
        $query->execute();
    }

    public function createCommand(array $panier, float $tva): Order
    {
        $order = new Order($panier['id'],Order::BEGIN,$tva);

        foreach($panier['list'] as $row)
        {
            $rowOrder = new OrderRow($row['quantity'],$row['ref'],$row['price'],$row['name']);
            $order->addRow($rowOrder);
        }

        return $order;
    }

    public function saveToDB(Order $order)
    {
        $this->database->getConnection()->beginTransaction();

        $query = $this->database->getConnection()->prepare(
            "INSERT IGNORE INTO boulangerie.order(cookie,statut,tva) ".
            "VALUES(:cookie,:statut,:tva) AS new ".
            "ON DUPLICATE KEY UPDATE statut = new.statut, tva = new.tva"
        );
        $query->bindParam("cookie",$order->getCookie(),PDO::PARAM_STR);
        $query->bindParam("statut",$order->getStatut(),PDO::PARAM_STR);
        $query->bindParam("tva",$order->getTva());
        $query->execute();

        /** @var OrderRow $row */
        foreach ($order->getRows() as $row) {

            $query = $this->database->getConnection()->prepare(
                "INSERT INTO boulangerie.order_row(quantity,ref,price,name,id_order) ".
                "VALUES(:quantity,:ref,:price,:name,:id_order) AS new ".
                "ON DUPLICATE KEY UPDATE quantity = new.quantity, price = new.price, name = new.name"
            );
            $query->bindParam("quantity",$row->getQuantity(),PDO::PARAM_INT);
            $query->bindParam("ref",$row->getRef(),PDO::PARAM_STR);
            $query->bindParam("price",$row->getPrice());
            $query->bindParam("name",$row->getName(),PDO::PARAM_STR);
            $query->bindParam("id_order",$order->getCookie(),PDO::PARAM_STR);
            $query->execute();
        }

        $this->database->getConnection()->commit();
    }

    public function getOrder(string $idCookie,array $store): Order
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT * FROM boulangerie.order LEFT JOIN boulangerie.order_row AS r ON r.id_order = cookie ".
            " WHERE boulangerie.order.cookie = :cookie_param"
        );
        $query->bindParam("cookie_param",$idCookie,PDO::PARAM_STR);

        $query->execute();
        $result = $query->fetchAll();

        $order = new Order($result[0]['cookie'],$result[0]['statut'],$result[0]['tva']);

        foreach ($result as $row) {
            if(is_null($store[$row['ref']])){
                continue;
            }
            $order->addRow(new OrderRow(
                $row['quantity'],
                $row['ref'],
                $row['price'],
                $row['name'],
                '/'.array_key_first($store[$row['ref']]['photo'])
            ));
        }

        return $order;
    }

    public function deleteOrder(string $id): void
    {
        $query = $this->database->getConnection()->prepare(
            "DELETE FROM boulangerie.order WHERE cookie = :cookie"
        );
        $query->bindParam("cookie",$id);
        $query->execute();
    }

    public function getFacture(string $id,?array $store = null):Facture
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT * FROM boulangerie.facture LEFT JOIN boulangerie.facture_row AS r ON r.id_facture = id".
            " WHERE boulangerie.facture.id = :id"
        );
        $query->bindParam("id",$id,PDO::PARAM_STR);

        $query->execute();
        $result = $query->fetchAll();

        $factureData = $result[0];

        $facture = new Facture($factureData['id'],$factureData['statut']);
        $facture->setEmail($factureData['email']);
        $facture->setFirstname($factureData['firstname']);
        $facture->setLastname($factureData['lastname']);
        $facture->setIdStripe($factureData['id_stripe']);
        $facture->setTva($factureData['tva']);
        $facture->setVat($factureData['tva_amount']);
        $facture->setPrice_ht($factureData['subtotal']);
        $facture->setPrice_ttc($factureData['total']);
        $facture->setNumero($factureData['numero']);
        $facture->setCreated_at(new DateTime($factureData['created_at']));

        $rows = [];

        foreach ($result as $row) {
            $rows[] =  new OrderRow(
                $row['quantity'],
                $row['ref'],
                $row['price'],
                $row['name'],
                !is_null($store) && isset($store[$row['ref']]['photo']) ? '/'.array_key_first($store[$row['ref']]['photo']) : ''
            );
        }

        $facture->setRows($rows);

        return $facture;
    }

    public function saveFacture(Facture $facture)
    {
        $this->database->getConnection()->beginTransaction();

        $query = $this->database->getConnection()->prepare(
            "INSERT INTO boulangerie.facture(id,firstname,lastname,email,id_stripe,statut,tva,tva_amount,subtotal,total,created_at) ".
            "VALUES(:id,:firstname,:lastname,:email,:id_stripe,:statut,:tva,:tva_amount,:subtotal,:total,NOW())"
        );

        $query->bindParam("id",$facture->getId(),PDO::PARAM_STR);
        $query->bindParam("firstname",$facture->getFirstname(),PDO::PARAM_STR);
        $query->bindParam("lastname",$facture->getLastname(),PDO::PARAM_STR);
        $query->bindParam("email",$facture->getEmail(),PDO::PARAM_STR);
        $query->bindParam("id_stripe",$facture->getIdStripe(),PDO::PARAM_STR);
        $query->bindParam("statut",$facture->getStatut(),PDO::PARAM_STR);
        $query->bindParam("tva",$facture->getTva());
        $query->bindParam("tva_amount",$facture->getVat());
        $query->bindParam("subtotal",$facture->getPrice_ht());
        $query->bindParam("total",$facture->getPrice_ttc());

        $query->execute();


        foreach ($facture->getRows() as $row) {
            
            $query = $this->database->getConnection()->prepare(
                "INSERT INTO boulangerie.facture_row(quantity,ref,price,name,id_facture) ".
                "VALUES(:quantity,:ref,:price,:name,:id_facture)"
            );

            $query->bindParam("quantity",$row->getQuantity(),PDO::PARAM_INT);
            $query->bindParam("ref",$row->getRef(),PDO::PARAM_STR);
            $query->bindParam("price",$row->getPrice());
            $query->bindParam("name",$row->getName(),PDO::PARAM_STR);
            $query->bindParam("id_facture",$facture->getId(),PDO::PARAM_STR);
            $query->execute();
        }

        $this->database->getConnection()->commit();
    }

    public function getListFactures(int $page,int $pagination): array
    {
        $offset = ($page-1)*$pagination;

        $query = $this->database->getConnection()->prepare(
            "SELECT * FROM boulangerie.facture AS f ORDER BY f.created_at DESC LIMIT :offset,:pagination"
        );

        $query->bindParam("offset",$offset,PDO::PARAM_INT);
        $query->bindParam("pagination",$pagination,PDO::PARAM_INT);

        $query->execute();

        $result = $query->fetchAll();

        $invoices = [];

        foreach ($result as $row) {
            $invoices[] = $this->hydrateFacture($row);
        }

        return $invoices;
    }

    public function getListFacturesByType(string $type,int $page,int $pagination): array
    {
        $offset = ($page-1)*$pagination;

        $query = $this->database->getConnection()->prepare(
            "SELECT * FROM boulangerie.facture AS f WHERE statut = :statut ORDER BY f.created_at DESC LIMIT :offset,:pagination"
        );

        $query->bindParam("offset",$offset,PDO::PARAM_INT);
        $query->bindParam("pagination",$pagination,PDO::PARAM_INT);
        $query->bindParam("statut",$type,PDO::PARAM_STR);

        $query->execute();

        $result = $query->fetchAll();

        $invoices = [];

        foreach ($result as $row) {
            $invoices[] = $this->hydrateFacture($row);
        }

        return $invoices;
    }

    public function countFactures():int
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT COUNT(*) FROM boulangerie.facture"
        );

        $query->execute();
        return $query->fetchColumn();
    }

    public function countFacturesByType(string $type):int
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT COUNT(*) FROM boulangerie.facture WHERE statut = :statut"
        );

        $query->bindParam("statut",$type,PDO::PARAM_STR);

        $query->execute();
        return $query->fetchColumn();
    }

    public function updateFactureStatut(Facture $facture): void
    {
        $query = $this->database->getConnection()->prepare(
            "UPDATE boulangerie.facture SET statut = :statut ".
            "WHERE id = :id"
        );

        $query->bindParam("statut",$facture->getStatut());
        $query->bindParam("id",$facture->getId());

        $query->execute();
    }

    private function hydrateFacture(array $rowData):Facture
    {
        $factureData = $rowData;

        $facture = new Facture($factureData['id'],$factureData['statut']);
        $facture->setEmail($factureData['email']);
        $facture->setFirstname($factureData['firstname']);
        $facture->setLastname($factureData['lastname']);
        $facture->setIdStripe($factureData['id_stripe']);
        $facture->setTva($factureData['tva']);
        $facture->setVat($factureData['tva_amount']);
        $facture->setPrice_ht($factureData['subtotal']);
        $facture->setPrice_ttc($factureData['total']);
        $facture->setNumero($factureData['numero']);
        $facture->setCreated_at(new DateTime($factureData['created_at']));

        return $facture;
    }

    public function getAmountToday():float
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT SUM(total) AS amount FROM boulangerie.facture ".
            " WHERE DAY(created_at) = DAY(NOW()) AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"
        );

        $query->execute();
        $result = $query->fetchColumn();
        return !is_null($result) ? $result: 0.00;
    }

    public function getAmountWeek():float
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT SUM(total) AS amount FROM boulangerie.facture ".
            " WHERE WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())"
        );

        $query->execute();
        $result = $query->fetchColumn();
        return !is_null($result) ? $result: 0.00;
    }

    public function getAmountYear():float
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT SUM(total) AS amount FROM boulangerie.facture WHERE YEAR(created_at) = YEAR(NOW())"
        );

        $query->execute();
        $result = $query->fetchColumn();
        return !is_null($result) ? $result: 0.00;
    }

    public function getAmountWait():float
    {
        $query = $this->database->getConnection()->prepare(
            "SELECT SUM(total) AS amount FROM boulangerie.facture WHERE statut = 'payed'"
        );

        $query->execute();
        $result = $query->fetchColumn();
        return !is_null($result) ? $result: 0.00;
    }
}