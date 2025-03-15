<?php

namespace Model;

class Student
{
    //try comment id
    public $id;
    public $student_id;
    public $name;
    public $embedding_face;
    public $status;

    public function __construct($student_id, $name, $embedding_face, $status)
    {
        $this->student_id = $student_id;
        $this->name = $name;
        $this->embedding_face = $embedding_face;
        $this->status = $status;
    }
}
