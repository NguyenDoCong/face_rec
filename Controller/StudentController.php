<?php

namespace Controller;

use Model\Student;
use Model\DBConnection;
use Model\StudentDB;
use Exception;

class StudentController
{
    public $studentDB;

    public function __construct()
    {
        $connection = new DBConnection("mysql:host=localhost;dbname=face_rec", "root", "");
        $this->studentDB = new StudentDB($connection->connect());
    }

    public function index()
    {
        include 'View/main.php';
    }
    
    public function add()
    {
        header('Content-Type: application/json');

        try {
            if (empty($_POST['student_id']) || empty($_POST['name']) || empty($_FILES['image'])) {
                $this->sendError("Thiếu thông tin bắt buộc");
            }

            $student_id = $_POST['student_id'];
            $name = $_POST['name'];
            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->sendError("Lỗi upload file");
            }

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = $student_id . "." . $file_extension;
            $upload_dir = dirname(__DIR__) . "/uploads/";

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_path = $upload_dir . $new_filename;
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                $this->sendError("Không thể lưu file");
            }

            $command = sprintf(
                'cd %s && python embedding_face.py %s 2>&1',
                dirname(__DIR__),
                $new_filename
            );

            $embedding_faces = shell_exec($command);
            if (empty(trim($embedding_faces))) {
                $this->sendError("Không thể tạo embedding face");
            }

            $embedding_face_json = json_encode(array_map('floatval', explode(',', trim($embedding_faces))));
            $student = new Student($student_id, $name, $embedding_face_json, 1);
            if (!$this->studentDB->create($student)) {
                $this->sendError("Không thể lưu thông tin sinh viên");
            }

            $this->sendSuccess('Thêm sinh viên thành công');
        } catch (\Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getAll()
    {
        $students = $this->studentDB->getAll();

        if ($students) {
            $this->send(200, 'success', $students);
        } else {
            $this->send(404, "No tasks found");
        }
    }

    public function check()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['embedding'])) {
                throw new Exception('No embedding data provided');
            }
            $face_embedding = $data['embedding'];
            $students = $this->studentDB->getAll();

            $best_match = null;
            $highest_similarity = 0;

            foreach ($students as $student) {
                $stored_embedding = json_decode($student->embedding_face, true);
                if ($stored_embedding === null) {
                    continue;
                }
                $similarity = $this->cosine_similarity($face_embedding, $stored_embedding);

                if ($similarity > $highest_similarity) {
                    $highest_similarity = $similarity;
                    $best_match = $student;
                }
            }

            if ($best_match && $highest_similarity > 0.3) {
                $response = [
                    'success' => true,
                    'student' => [
                        'id' => $best_match->id,
                        'student_id' => $best_match->student_id,
                        'name' => $best_match->name,
                        'similarity' => $highest_similarity
                    ]
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No matching student found'
                ];
            }
        } catch (Exception $e) {
            http_response_code(400);
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        echo json_encode($response);
    }

    private function cosine_similarity($vec1, $vec2)
    {
        if ($vec2 === null) {
            throw new \InvalidArgumentException("Second argument for cosine similarity is null");
        }
        $dot_product = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $vec1[$i] = (float) $vec1[$i];
            $vec2[$i] = (float) $vec2[$i];
            $dot_product += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        return $dot_product / ($norm1 * $norm2);
    }

    private function send($status, $message, $data = null)
    {
        header("Content-Type: application/json");
        http_response_code($status);
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    private function sendError($message)
    {
        $this->send(400, $message);
    }

    private function sendSuccess($message)
    {
        $this->send(200, $message);
    }
}
