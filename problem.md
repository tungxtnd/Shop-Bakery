 ### 1. Phân tích các ý bạn đã nêu (Refining your points)                                                               
 #### 🟢 Ý 1: Sửa logic trừ kho (Inventory Timing)                                                              
 - Xác nhận: Đây là ưu tiên số 1.                     
 - Chi tiết kỹ thuật: Chuyển từ "Deduction on Order Creation" $\rightarrow$ "Deduction on Payment Confirmation (IPN)".                                                
 #### 🟢 Ý 2: Thêm "Objective SQL" (Nâng cấp truy vấn)                                                                  
 các truy vấn SQL của tôi chưa đảm bảo objective SQL 
 #### 🟢 Ý 3: Quy trình đặt hàng cho người khác (Ordering for others)                                                               
 - Xác nhận: Đây là thực tế của ngành F&B (khách đặt hộ, đặt quà tặng). 
 - Cần làm:                                                                  
     - UI/UX: Khi user thực hiện flow: view product details -> add to cart -> checkout -> thì ở đây cần phải thêm bước xác nhận thông tin người nhận, nếu người đặt và người nhận khác nhau chứ không được lấy thông tin mặc định trong profile    
   
 #### 🟢 Ý 4: Quy trình Hủy đơn (Cancellation Workflow)                                                                                                                
 - Xác nhận: Đây là tính năng cực kỳ quan trọng để quản lý dòng tiền và kho
 User có quyền hủy đơn không và nếu hủy đơn hàng thì ở giai đoạn thời gian nào? nếu hủy đơn hàng thì hoàn tiền sẽ như thế nào? 
 