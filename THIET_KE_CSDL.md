# THIẾT KẾ CƠ SỞ DỮ LIỆU - HỆ THỐNG QUẢN LÝ ĐĂNG KÝ NGHIÊN CỨU KHOA HỌC

## 1. CÁC THỰC THỂ VÀ THUỘC TÍNH

### 1.1. Người dùng (Users)
- **Mã người dùng** (id) - Khóa chính, tự động tăng
- **Tên** (name) - Tên đầy đủ của người dùng
- **Email** (email) - Địa chỉ email duy nhất
- **Mật khẩu** (password) - Mật khẩu đã mã hóa
- **Vai trò** (role) - Quyền người dùng: admin, lecturer, student
- **Email đã xác thực** (email_verified_at) - Thời gian xác thực email
- **Thời gian tạo** (created_at) - Thời gian tạo tài khoản
- **Thời gian cập nhật** (updated_at) - Thời gian cập nhật cuối

### 1.2. Sinh viên (Students)
- **Mã sinh viên** (id) - Khóa chính, tự động tăng
- **Mã người dùng** (user_id) - Khóa ngoại liên kết với bảng users
- **Mã số sinh viên** (student_id) - Mã số sinh viên duy nhất
- **Lớp** (class) - Lớp học của sinh viên
- **Chuyên ngành** (major) - Chuyên ngành học
- **Thời gian tạo** (created_at) - Thời gian tạo hồ sơ
- **Thời gian cập nhật** (updated_at) - Thời gian cập nhật cuối

### 1.3. Giảng viên (Lecturers)
- **Mã giảng viên** (id) - Khóa chính, tự động tăng
- **Mã người dùng** (user_id) - Khóa ngoại liên kết với bảng users
- **Mã số giảng viên** (lecturer_id) - Mã số giảng viên duy nhất
- **Khoa/Bộ môn** (department) - Khoa hoặc bộ môn công tác
- **Học vị** (title) - Học vị của giảng viên
- **Chuyên môn** (specialization) - Lĩnh vực chuyên môn
- **Trạng thái** (status) - Trạng thái hoạt động: active, inactive
- **Số sinh viên tối đa** (max_students) - Số lượng sinh viên tối đa có thể hướng dẫn
- **Thời gian tạo** (created_at) - Thời gian tạo hồ sơ
- **Thời gian cập nhật** (updated_at) - Thời gian cập nhật cuối

### 1.4. Đề tài nghiên cứu (Proposals)
- **Mã đề tài** (id) - Khóa chính, tự động tăng
- **Tiêu đề** (title) - Tên đề tài nghiên cứu
- **Lĩnh vực** (field) - Lĩnh vực nghiên cứu
- **Mô tả** (description) - Mô tả chi tiết đề tài
- **Mã giảng viên** (lecturer_id) - Khóa ngoại liên kết với bảng lecturers
- **Mã sinh viên** (student_id) - Khóa ngoại liên kết với bảng students (có thể null)
- **Trạng thái** (status) - Trạng thái đề tài: draft, active, completed, cancelled
- **Hiển thị** (is_visible) - Có hiển thị công khai hay không
- **Thời gian tạo** (created_at) - Thời gian tạo đề tài
- **Thời gian cập nhật** (updated_at) - Thời gian cập nhật cuối

### 1.5. Lời mời hướng dẫn (Invitations)
- **Mã lời mời** (id) - Khóa chính, tự động tăng
- **Mã sinh viên** (student_id) - Khóa ngoại liên kết với bảng students
- **Mã giảng viên** (lecturer_id) - Khóa ngoại liên kết với bảng lecturers
- **Mã đề tài** (proposal_id) - Khóa ngoại liên kết với bảng proposals (có thể null)
- **Tin nhắn** (message) - Nội dung tin nhắn kèm theo lời mời
- **Trạng thái** (status) - Trạng thái lời mời: pending, accepted, rejected
- **Thời gian xử lý** (processed_at) - Thời gian giảng viên xử lý lời mời
- **Thời gian hết hạn** (expires_at) - Thời gian hết hạn lời mời
- **Thời gian tạo** (created_at) - Thời gian gửi lời mời
- **Thời gian cập nhật** (updated_at) - Thời gian cập nhật cuối

## 2. CÁC MỐI LIÊN KẾT

### 2.1. Mối quan hệ 1-1
- **Người dùng (1-1) → Sinh viên**: Một tài khoản người dùng thuộc về một sinh viên cụ thể
- **Người dùng (1-1) → Giảng viên**: Một tài khoản người dùng thuộc về một giảng viên cụ thể

### 2.2. Mối quan hệ 1-N
- **Giảng viên (1-N) → Đề tài**: Mỗi giảng viên có thể tạo nhiều đề tài nghiên cứu, nhưng một đề tài chỉ thuộc về một giảng viên
- **Sinh viên (1-N) → Lời mời**: Một sinh viên có thể gửi nhiều lời mời đến các giảng viên khác nhau
- **Giảng viên (1-N) → Lời mời**: Một giảng viên có thể nhận nhiều lời mời từ các sinh viên khác nhau
- **Đề tài (1-N) → Lời mời**: Một đề tài có thể có nhiều lời mời liên quan

### 2.3. Mối quan hệ N-1
- **Sinh viên (N-1) → Đề tài**: Một sinh viên có thể được gán vào một đề tài cụ thể (thông qua trường student_id trong bảng proposals)

### 2.4. Mối quan hệ tùy chọn
- **Đề tài (0-1) → Sinh viên**: Một đề tài có thể chưa được gán cho sinh viên nào (student_id = null)
- **Lời mời (0-1) → Đề tài**: Một lời mời có thể không liên quan đến đề tài cụ thể nào (proposal_id = null)

## 3. RÀNG BUỘC VÀ QUY TẮC NGHIỆP VỤ

### 3.1. Ràng buộc toàn vẹn
- Email trong bảng users phải là duy nhất
- Mã số sinh viên (student_id) phải là duy nhất
- Mã số giảng viên (lecturer_id) phải là duy nhất
- Khi xóa người dùng, tự động xóa hồ sơ sinh viên/giảng viên tương ứng (cascade)
- Khi xóa giảng viên, tự động xóa tất cả đề tài và lời mời liên quan (cascade)
- Khi xóa sinh viên, tự động xóa tất cả lời mời liên quan (cascade)

### 3.2. Quy tắc nghiệp vụ
- Giảng viên chỉ có thể hướng dẫn tối đa số lượng sinh viên được quy định (max_students)
- Lời mời có thời hạn hiệu lực (expires_at)
- Đề tài có các trạng thái: nháp, hoạt động, hoàn thành, hủy bỏ
- Lời mời có các trạng thái: chờ xử lý, chấp nhận, từ chối