<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Index</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
        crossorigin="anonymous">
</head>

<body>
    <div class="col-12 col-md-12">
        <div class="row">

            <div class="col-4">
                <h1>Thêm Sinh Viên</h1>
                <form method="post" id="addForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Mã Sinh Viên:</label>
                        <input type="text" class="form-control" name="student_id" placeholder="Nhập mã SV" required id="student_id">
                    </div>
                    <div class="form-group">
                        <label>Tên Sinh Viên</label>
                        <input type="text" class="form-control" name="name" placeholder="Nhập tên SV" required id="name">
                    </div>
                    <div class="form-group">
                        <label>Chọn ảnh để upload</label>
                        <input type="file" name="image" id="image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </form>
            </div>
            <div class="col-8">
                <h1>Danh Sách Sinh Viên</h1>
                <table class="table" id="studentList">
                    <thead>
                        <tr>
                            <th scope="col">Mã Sinh Viên</th>
                            <th scope="col">Tên Sinh Viên</th>
                            <th scope="col">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody id="studentList">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="http://localhost/face_rec/assets/js/add.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
    integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
    crossorigin="anonymous"></script>

</html>