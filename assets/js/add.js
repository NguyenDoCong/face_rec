$("#addForm").submit(function (event) {
  event.preventDefault();

  const student_id = $("#student_id").val().trim();
  const name = $("#name").val().trim();
  const imageFile = $("#image")[0].files[0];

  if (!student_id || !name || !imageFile) {
    alert("Vui lòng nhập đầy đủ thông tin và chọn ảnh");
    return;
  }

  const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
  if (!allowedTypes.includes(imageFile.type)) {
    alert("Chỉ chấp nhận file ảnh định dạng JPG, JPEG hoặc PNG");
    return;
  }

  if (imageFile.size > 5 * 1024 * 1024) {
    alert("Kích thước file không được vượt quá 5MB");
    return;
  }

  let formData = new FormData();
  formData.append("student_id", student_id);
  formData.append("name", name);
  formData.append("image", imageFile);
  formData.append("status", 1);

  const submitBtn = $(this).find('button[type="submit"]');
  const originalText = submitBtn.text();
  submitBtn.prop("disabled", true).text("Đang xử lý...");

  $.ajax({
    url: "index.php?controller=Student&action=add",
    method: "POST",
    processData: false,
    contentType: false,
    data: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    success: function (response) {
      if (typeof response === "object") {
        handleResponse(response);
      } else {
        const jsonResponse = JSON.parse(response);
        handleResponse(jsonResponse);
      }
    },
    error: function (xhr, status, error) {
      alert("Lỗi kết nối đến server: " + error + "\nStatus: " + status);
    },
    complete: function () {
      submitBtn.prop("disabled", false).text(originalText);
    },
  });
});

function handleResponse(response) {
  if (response.success) {
    alert(response.message);
    window.location.href = "http://localhost/face_rec/";
  } else {
    alert("Lỗi: " + (response.message || "Không thể thêm sinh viên"));
  }
}

function render(students) {
  let tableBody = $("#studentList tbody");
  tableBody.empty();

  students.forEach(function (student) {
    tableBody.append(`
      <tr>
        <td>${student.student_id}</td>
        <td>${student.name}</td>
        <td>${student.status}</td>
      </tr>
    `);
  });
}

function getstudents() {
  $.ajax({
    url: "http://localhost/face_rec/index.php?controller=Student&action=getAll",
    method: "GET",
    success: function (response) {
      if (response.status === 200) {
        render(response.data);
      } else {
        alert(response.message || "Failed to fetch students.");
      }
    },
    error: function (xhr, status, error) {
      alert("Error fetching students: " + error);
    },
  });
}

$(document).ready(function () {
  getstudents();
});
