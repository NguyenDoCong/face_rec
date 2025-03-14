<?php

namespace Model;

use PDO;

class StudentDB
{
    public $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }


    public function create($student)
    {
        $sql = "INSERT INTO students (student_id, name, embedding_face, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            $student->student_id,
            $student->name,
            $student->embedding_face,
            $student->status
        ]);
    }

    public function get($id)
    {
        $sql = "SELECT * FROM students WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            $student = new Student($row['student_id'], $row['name'], $row['embedding_face'], $row['status']);
            $student->id = $row['id'];
            return $student;
        }
        return null;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM students";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function($row) {
            return new Student($row['student_id'], $row['name'], $row['embedding_face'], $row['status']);
        }, $rows);
    }
}
