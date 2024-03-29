<?php
/*
 *  Метод контструкт и свойства класса продукт
 */

class Product
{

    //подключение к базе данных и имя таблицы
    private $conn;
    private $table_name = "products";

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $category_name;
    public $created;

    // метод конструкт для соединения с БД
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Метод вывести продукт
    function read()
    {

        // весь запрос
        $query = "SELECT
                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
            FROM
                " . $this->table_name . " p
                LEFT JOIN
                    categories c
                        ON p.category_id = c.id
            ORDER BY
                p.created DESC";

        // подготовить запрос
        $stmt = $this->conn->prepare($query);

        // выполнить
        $stmt->execute();

        return $stmt;
    }


    // Вывести один продукт
    function readOne()
    {

        // запрос на чтение одной записи
        $query = "SELECT
                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
            FROM
                " . $this->table_name . " p
                LEFT JOIN
                    categories c
                        ON p.category_id = c.id
            WHERE
                p.id = ?
            LIMIT
                0,1";


        // подготовить оператор запроса
        $stmt = $this->conn->prepare($query);

        // Привязывает параметр запроса к переменной
        $stmt->bindParam(1, $this->id); // https://www.php.net/manual/ru/pdostatement.bindparam.php

        // выполнить запрос
        $stmt->execute();

        //получить извлеченную строку
        $row = $stmt->fetch(PDO::FETCH_ASSOC); // https://www.php.net/manual/ru/pdostatement.fetch.php

        // установить занчения в свойства объекта
        $this->name = $row['name'];
        $this->price = $row['price'];
        $this->description = $row['description'];
        $this->category_id = $row['category_id'];
        $this->category_name = $row['category_name'];
    }


    // поиск продукта по параметрам
    function search($keywords)
    {

        // выбрать всё
        $query = "SELECT
                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
            FROM
                " . $this->table_name . " p
                LEFT JOIN
                    categories c
                        ON p.category_id = c.id
            WHERE
                p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?
            ORDER BY
                p.created DESC";

        $stmt = $this->conn->prepare($query);

        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%"; // Эернаирование значений ключевых слов (что-то с безопасностью)

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        $stmt->execute();

        return $stmt;
    }

    // вывести продукты с пагинацией
    public function readPaging($from_record_num, $records_per_page)
    {

        $query = "SELECT
                c.name as category_name, p.id, p.name, p.description, p.price, p.category_id, p.created
            FROM
                " . $this->table_name . " p
                LEFT JOIN
                    categories c
                        ON p.category_id = c.id
            ORDER BY p.created DESC
            LIMIT ?, ?";


        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }

    // посчитать пагинацию
    public function count()
    {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . " ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_rows'];
    }
}
