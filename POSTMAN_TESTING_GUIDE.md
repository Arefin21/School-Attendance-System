# Postman Testing Guide

## Quick Start

### Step 1: Import the Collection

1. Open Postman
2. Click **Import** button (top left)
3. Select the file: `School-Attendance-API.postman_collection.json`
4. Click **Import**

### Step 2: Set Up Environment Variables

The collection uses two variables:
- `base_url`: Your API base URL (default: `http://localhost:8000`)
- `auth_token`: Auto-populated after login

**Option A: Use Collection Variables (Recommended)**
- The variables are already set in the collection
- Make sure your Laravel server is running on `http://localhost:8000`

**Option B: Create a New Environment**
1. Click **Environments** (left sidebar)
2. Click **+** to create new environment
3. Add variables:
   - `base_url`: `http://localhost:8000`
   - `auth_token`: (leave empty, will auto-populate)
4. Save and select the environment

### Step 3: Start Your Laravel Server

```bash
php artisan serve
```

Make sure Redis is also running for caching features.

## Testing Workflow

### 1. Authentication Flow

**A. Login (Required for protected routes)**

1. Open: **Authentication → Login**
2. Use default credentials:
   ```json
   {
       "email": "admin@school.com",
       "password": "password"
   }
   ```
3. Click **Send**
4. The `auth_token` will be automatically saved to your environment
5. You're now authenticated!

**B. Register New User (Optional)**

1. Open: **Authentication → Register**
2. Modify the email/name as needed
3. Click **Send**

**C. Get Current User Info**

1. Open: **Authentication → Get Current User**
2. Click **Send** (token is auto-included)

### 2. Testing Students API

**Public Routes (No Auth Required):**

1. **Get All Students**
   - Open: **Students → Get All Students**
   - Click **Send**
   - Should return all 50 seeded students

2. **Filter by Class**
   - Open: **Students → Filter Students by Class**
   - Modify query param `class` (1-10)
   - Click **Send**

3. **Filter by Class and Section**
   - Open: **Students → Filter Students by Class and Section**
   - Modify `class` and `section` params
   - Click **Send**

4. **Search by Name**
   - Open: **Students → Search Students by Name**
   - Modify `search` param
   - Click **Send**

5. **Get Single Student**
   - Open: **Students → Get Single Student**
   - Change the ID in URL (e.g., `/api/students/1`)
   - Click **Send**

**Protected Routes (Auth Required):**

6. **Create Student (JSON)**
   - Make sure you're logged in first!
   - Open: **Students → Create Student (JSON)**
   - Modify the JSON body:
     ```json
     {
         "name": "Your Name",
         "student_id": "STU9999",
         "class": "5",
         "section": "A"
     }
     ```
   - Click **Send**
   - Should return status `201 Created`

7. **Create Student with Photo**
   - Open: **Students → Create Student with Photo**
   - In Body tab (form-data):
     - Fill in text fields
     - For `photo`: Click file picker and select an image
   - Click **Send**

8. **Update Student**
   - Open: **Students → Update Student**
   - Change URL ID to student you want to update
   - Modify fields you want to change
   - Click **Send**

9. **Delete Student**
   - Open: **Students → Delete Student**
   - Change URL ID
   - Click **Send**

### 3. Testing Attendance API

**Public Routes:**

1. **Get All Attendance**
   - Open: **Attendance → Get All Attendance Records**
   - Click **Send**

2. **Filter by Date**
   - Open: **Attendance → Filter Attendance by Date**
   - Modify date param (format: `YYYY-MM-DD`)
   - Click **Send**

3. **Filter by Status**
   - Open: **Attendance → Filter Attendance by Status**
   - Status values: `present`, `absent`, `late`
   - Click **Send**

4. **Filter by Student**
   - Open: **Attendance → Filter Attendance by Student**
   - Modify `student_id` param
   - Click **Send**

**Protected Routes:**

5. **Record Single Attendance**
   - Login first!
   - Open: **Attendance → Record Single Attendance**
   - Modify JSON:
     ```json
     {
         "student_id": 1,
         "date": "2025-11-16",
         "status": "present",
         "note": "On time"
     }
     ```
   - Status must be: `present`, `absent`, or `late`
   - Click **Send**
   - Returns status `201 Created`

6. **Record Bulk Attendance**
   - Open: **Attendance → Bulk Record Attendance**
   - This allows recording multiple students at once
   - Modify the JSON array as needed
   - Click **Send**

7. **Update Attendance**
   - Open: **Attendance → Update Attendance**
   - Change URL ID
   - Modify status or note
   - Click **Send**

8. **Delete Attendance**
   - Open: **Attendance → Delete Attendance**
   - Change URL ID
   - Click **Send**

### 4. Testing Reports & Statistics

**All reports are public (no auth required):**

1. **Today's Summary**
   - Open: **Reports & Statistics → Get Today's Summary**
   - Click **Send**
   - Returns:
     ```json
     {
         "date": "2025-11-16",
         "total_students": 50,
         "present": 40,
         "absent": 8,
         "late": 2,
         "attendance_percentage": 80
     }
     ```

2. **Stats by Specific Date**
   - Open: **Reports & Statistics → Get Stats by Date**
   - Modify `date` param
   - Click **Send**
   - Returns stats + list of students with their attendance

3. **Monthly Report (All Classes)**
   - Open: **Reports & Statistics → Get Monthly Report (All Classes)**
   - Modify `year` and `month` params
   - Click **Send**
   - Returns detailed report for all students

4. **Monthly Report (Specific Class)**
   - Open: **Reports & Statistics → Get Monthly Report (Specific Class)**
   - Modify `year`, `month`, and `class` params
   - Click **Send**
   - Returns report filtered by class

## Common Issues & Solutions

### Issue: "Unauthenticated" Error
**Solution**: 
- Run the **Login** request first
- Make sure the `auth_token` variable is set
- Check if token is included in Authorization header

### Issue: "The student id has already been taken"
**Solution**: 
- Use a unique `student_id` when creating students
- Try: `STU` + random 4 digits (e.g., `STU7654`)

### Issue: "The student has already been marked for this date"
**Solution**: 
- You can only record attendance once per student per day
- Use **Update Attendance** to modify existing records
- Or use a different date

### Issue: Connection Refused
**Solution**: 
- Make sure Laravel is running: `php artisan serve`
- Check `base_url` matches your server URL
- Default is `http://localhost:8000`

### Issue: Empty Reports/No Data
**Solution**: 
- Run seeders: `php artisan migrate:fresh --seed`
- This creates 50 students with 30 days of attendance

## Testing Redis Caching

To verify caching is working:

1. **First Request** (Cache Miss)
   - Open: **Reports & Statistics → Get Today's Summary**
   - Click **Send**
   - Note the response time

2. **Second Request** (Cache Hit)
   - Click **Send** again immediately
   - Response should be faster (cached)

3. **Invalidate Cache**
   - Record new attendance for any student
   - This clears the cache

4. **Third Request** (Cache Miss Again)
   - Request the summary again
   - Slower response (rebuilding cache)

## Status Codes Reference

- `200 OK` - Successful GET, PUT, DELETE
- `201 Created` - Successful POST (resource created)
- `204 No Content` - Successful DELETE (no body)
- `401 Unauthorized` - Not logged in or invalid token
- `404 Not Found` - Resource doesn't exist
- `422 Unprocessable Entity` - Validation failed

## JSON Response Formats

### Success Response (Single Resource)
```json
{
    "id": 1,
    "name": "John Doe",
    "student_id": "STU1234",
    "class": "5",
    "section": "A",
    "photo": null,
    "created_at": "2025-11-16T10:00:00.000000Z",
    "updated_at": "2025-11-16T10:00:00.000000Z"
}
```

### Success Response (Collection with Pagination)
```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "total": 50,
        "per_page": 15
    }
}
```

### Error Response (Validation)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "student_id": [
            "The student id has already been taken."
        ]
    }
}
```

## Tips for Efficient Testing

1. **Use Collection Runner**
   - Click **Run Collection**
   - Test all endpoints automatically
   - Great for regression testing

2. **Save Responses as Examples**
   - Click **Save as Example** after successful requests
   - Helps document expected responses

3. **Use Pre-request Scripts**
   - Auto-generate test data
   - Set dynamic dates

4. **Create Test Scripts**
   - Validate response structure
   - Check status codes automatically

5. **Organize with Folders**
   - Already organized by feature
   - Easy to find specific endpoints

## Next Steps

After testing all endpoints:

1. ✅ Verify all CRUD operations work
2. ✅ Test validation errors (try invalid data)
3. ✅ Test authentication flow
4. ✅ Verify caching with reports
5. ✅ Test bulk operations
6. ✅ Check pagination works
7. ✅ Test all filters and search

Happy Testing! 🚀

