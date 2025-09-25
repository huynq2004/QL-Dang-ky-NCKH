# HƯỚNG DẪN KIỂM THỬ API XỬ LÝ LỜI MỜI

## Endpoint mới đã thêm:
```
PUT /invitations/{invitation_id}/process
```

## Cách sử dụng:

### 1. **Chấp nhận lời mời (Giảng viên)**
```bash
curl -X PUT "http://your-domain/invitations/1/process" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"action": "accept"}'
```

### 2. **Từ chối lời mời (Giảng viên)**
```bash
curl -X PUT "http://your-domain/invitations/1/process" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"action": "reject"}'
```

### 3. **Thu hồi lời mời (Sinh viên)**
```bash
curl -X PUT "http://your-domain/invitations/1/process" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"action": "withdraw"}'
```

## Response Format:

### **Thành công (200 OK):**
```json
{
  "success": true,
  "message": "Chấp nhận lời mời thành công"
}
```

### **Lỗi quyền truy cập (403 Forbidden):**
```json
{
  "success": false,
  "message": "Không có quyền xử lý lời mời này"
}
```

### **Lỗi validation (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Không thể thu hồi: quá 24 giờ kể từ khi gửi"
}
```

## Test Cases có thể áp dụng:

| TC | Action | User Role | Expected Result |
|----|--------|-----------|-----------------|
| TC1 | accept | lecturer (recipient) | 200 OK |
| TC2 | reject | lecturer (recipient) | 200 OK |
| TC3 | withdraw | student (sender, <24h) | 200 OK |
| TC4 | withdraw | student (sender, >24h) | 422 Error |
| TC5 | accept | student | 403 Forbidden |
| TC6 | withdraw | lecturer | 403 Forbidden |

## Lưu ý:
- Endpoint cũ vẫn hoạt động bình thường
- Endpoint mới hỗ trợ cả JSON response và redirect response
- Tự động detect request type để trả về format phù hợp
