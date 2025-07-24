# 🔄 ILab UNMUL - User Flow Diagram

Comprehensive user flow diagram untuk semua user roles dan system workflows dalam sistem ILab UNMUL.

## 📊 System Overview Flow

```mermaid
graph TD
    A[👤 Visitor] --> B{Has Account?}
    B -->|No| C[📝 Register]
    B -->|Yes| D[🔑 Login]
    C --> E[✉️ Email Verification]
    E --> D
    D --> F{User Role?}
    
    F -->|Admin| G[⚙️ Admin Dashboard]
    F -->|Faculty| H[👨‍🏫 Faculty Dashboard]
    F -->|Student| I[🎓 Student Dashboard]
    F -->|External| J[🏢 External Dashboard]
    
    G --> K[🔧 System Management]
    H --> L[📋 Research Booking]
    I --> M[📚 Student Booking]
    J --> N[💼 Commercial Booking]
    
    K --> O[📊 Reports & Analytics]
    L --> P[🔬 Lab Access]
    M --> P
    N --> P
```

## 🎯 Detailed User Flows

### 1. 👤 Guest User Flow

```mermaid
graph TD
    A[🌐 Homepage] --> B[📖 Browse Information]
    B --> C[📋 View Services]
    B --> D[🔬 View Equipment]
    B --> E[📧 Contact Form]
    B --> F[📝 Register Account]
    
    C --> C1[Analisis Kimia]
    C --> C2[Material Testing]
    C --> C3[Kalibrasi]
    C --> C4[Pelatihan]
    
    D --> D1[Search Equipment]
    D --> D2[Filter by Category]
    D --> D3[View Availability]
    
    E --> E1[Send Message]
    E1 --> E2[✅ Message Sent]
    
    F --> F1[Fill Registration Form]
    F1 --> F2[Select Role]
    F2 --> F3[Submit Application]
    F3 --> F4[✉️ Verification Email]
    F4 --> F5[✅ Account Created]
```

### 2. 🔑 Authentication Flow

```mermaid
graph TD
    A[🔑 Login Page] --> B[📝 Enter Credentials]
    B --> C{Valid?}
    
    C -->|❌ No| D[⚠️ Error Message]
    D --> B
    
    C -->|✅ Yes| E{User Role?}
    
    E -->|staf_ilab| F[⚙️ Admin Dashboard]
    E -->|fakultas| G[👨‍🏫 Faculty Dashboard]
    E -->|mahasiswa| H[🎓 Student Dashboard]
    E -->|peneliti_internal| I[🔬 Researcher Dashboard]
    E -->|industri| J[🏭 Industry Dashboard]
    E -->|pemerintah| K[🏛️ Government Dashboard]
    E -->|masyarakat| L[👥 Public Dashboard]
    E -->|umkm| M[🏪 UMKM Dashboard]
    
    F --> N[Full System Access]
    G --> O[Research Booking Access]
    H --> P[Student Booking Access]
    I --> Q[Internal Research Access]
    J --> R[Commercial Service Access]
    K --> S[Policy Research Access]
    L --> T[Basic Service Access]
    M --> U[Business Support Access]
```

### 3. 📋 Booking System Flow

```mermaid
graph TD
    A[📋 Start Booking] --> B[🔑 Login Required]
    B --> C[📝 Booking Form]
    
    C --> D[Step 1: Service Selection]
    D --> D1[Select Category]
    D1 --> D2[Select Service Type]
    D2 --> D3[View Pricing]
    
    D3 --> E[Step 2: Schedule Selection]
    E --> E1[📅 Select Date]
    E1 --> E2[⏰ Select Time Slot]
    E2 --> E3[✅ Check Availability]
    
    E3 --> F[Step 3: Equipment Selection]
    F --> F1[🔬 Browse Equipment]
    F1 --> F2[Check Equipment Status]
    F2 --> F3[Reserve Equipment]
    
    F3 --> G[Step 4: Sample Information]
    G --> G1[📝 Sample Description]
    G1 --> G2[📎 Upload Files]
    G2 --> G3[Special Requirements]
    
    G3 --> H[Step 5: Review & Submit]
    H --> H1[💰 View Cost Estimate]
    H1 --> H2[📋 Review Details]
    H2 --> H3[✅ Accept Terms]
    
    H3 --> I[🔄 Submit Booking]
    I --> J[🎫 Generate Booking Code]
    J --> K[✉️ Send Confirmation Email]
    K --> L[📊 Update Dashboard]
    
    L --> M{Admin Review}
    M -->|Approved| N[✅ Booking Approved]
    M -->|Rejected| O[❌ Booking Rejected] 
    M -->|Needs Info| P[ℹ️ Request Information]
    
    N --> Q[📧 Approval Email]
    O --> R[📧 Rejection Email]
    P --> S[📧 Information Request]
    
    Q --> T[🔬 Lab Session]
    T --> U[📋 Complete Service]
    U --> V[📄 Generate Report]
    V --> W[✅ Close Booking]
```

### 4. ⚙️ Admin Management Flow

```mermaid
graph TD
    A[⚙️ Admin Dashboard] --> B[📊 System Overview]
    B --> B1[Active Users Count]
    B --> B2[Pending Bookings]
    B --> B3[Equipment Status]
    B --> B4[Revenue Statistics]
    
    A --> C[👥 User Management]
    C --> C1[View All Users]
    C1 --> C2[Edit User Details]
    C1 --> C3[Change User Role]
    C1 --> C4[Activate/Deactivate]
    C1 --> C5[Delete User]
    
    A --> D[📋 Booking Management]
    D --> D1[View Pending Bookings]
    D --> D2[Review Booking Details]
    D --> D3{Decision}
    
    D3 -->|Approve| D4[✅ Approve Booking]
    D3 -->|Reject| D5[❌ Reject Booking]
    D3 -->|Request Info| D6[ℹ️ Request Information]
    
    D4 --> D7[📧 Send Approval Email]
    D5 --> D8[📧 Send Rejection Email]
    D6 --> D9[📧 Send Info Request]
    
    A --> E[🔧 Equipment Management]
    E --> E1[Add New Equipment]
    E --> E2[Update Equipment Status]
    E --> E3[Schedule Maintenance]
    E --> E4[Calibration Management]
    
    A --> F[📄 Reports & Analytics]
    F --> F1[Generate Usage Reports]
    F --> F2[Financial Reports]
    F --> F3[Equipment Utilization]
    F --> F4[User Activity Reports]
```

### 5. 👤 User Dashboard Flow

```mermaid
graph TD
    A[👤 User Dashboard] --> B[📊 Personal Overview]
    B --> B1[Active Bookings Count]
    B --> B2[Completed Services]
    B --> B3[Pending Requests]
    B --> B4[Account Status]
    
    A --> C[📋 My Bookings]
    C --> C1[View All Bookings]
    C1 --> C2[Filter by Status]
    C1 --> C3[Search Bookings]
    C1 --> C4[View Booking Details]
    
    C4 --> C5{Booking Status?}
    C5 -->|Pending| C6[Cancel Booking]
    C5 -->|Approved| C7[Download Confirmation]
    C5 -->|Completed| C8[Download Report]
    C5 -->|In Progress| C9[Track Progress]
    
    A --> D[📝 New Booking]
    D --> D1[Quick Booking Form]
    D1 --> D2[Service Selection]
    D2 --> D3[Schedule Selection]
    D3 --> D4[Submit Request]
    
    A --> E[⚙️ Profile Management]
    E --> E1[Edit Personal Info]
    E --> E2[Change Password]
    E --> E3[Update Institution Details]
    E --> E4[Notification Preferences]
    
    A --> F[📞 Support]
    F --> F1[Contact Form]
    F --> F2[FAQ Section]
    F --> F3[Download SOPs]
    F --> F4[Training Materials]
```

### 6. 🔬 Equipment Management Flow

```mermaid
graph TD
    A[🔬 Equipment Catalog] --> B[📋 Browse Equipment]
    B --> B1[Filter by Category]
    B --> B2[Search by Name]
    B --> B3[Filter by Availability]
    
    B1 --> C1[Chromatography]
    B1 --> C2[Spectroscopy]
    B1 --> C3[Material Testing]
    B1 --> C4[General Lab]
    
    B --> D[📄 Equipment Details]
    D --> D1[Specifications]
    D --> D2[Availability Calendar]
    D --> D3[Booking History]
    D --> D4[Maintenance Schedule]
    
    D2 --> E{Available?}
    E -->|✅ Yes| F[📋 Book Equipment]
    E -->|❌ No| G[📅 Check Next Available]
    E -->|🔧 Maintenance| H[⏳ Maintenance Schedule]
    
    F --> I[Select Time Slot]
    I --> J[Add to Booking Request]
    J --> K[Continue Booking Process]
    
    G --> L[📧 Notify When Available]
    H --> M[📋 View Maintenance Details]
```

### 7. 📧 Email Notification Flow

```mermaid
graph TD
    A[🔄 System Event] --> B{Event Type?}
    
    B -->|User Registration| C[📧 Welcome Email]
    B -->|Booking Created| D[📧 Booking Confirmation]
    B -->|Booking Approved| E[📧 Approval Notification]
    B -->|Booking Rejected| F[📧 Rejection Notification]
    B -->|Service Complete| G[📧 Completion Notice]
    
    C --> C1[✉️ Send to User]
    C1 --> C2[📋 Log Email Activity]
    
    D --> D1[✉️ Send to User]
    D --> D2[✉️ Send to Admin]
    D1 --> D3[📋 Log Activity]
    D2 --> D3
    
    E --> E1[✉️ Send to User]
    E1 --> E2[📋 Include Instructions]
    E2 --> E3[📋 Log Activity]
    
    F --> F1[✉️ Send to User]
    F1 --> F2[📋 Include Reason]
    F2 --> F3[📋 Log Activity]
    
    G --> G1[✉️ Send to User]
    G1 --> G2[📎 Attach Report]
    G2 --> G3[📋 Log Activity]
```

### 8. 🔒 Security & File Management Flow

```mermaid
graph TD
    A[📎 File Upload] --> B[🔍 Security Validation]
    B --> B1[Check File Size]
    B --> B2[Validate Extension]
    B --> B3[MIME Type Check]
    B --> B4[File Signature Validation]
    B --> B5[Malware Scan]
    
    B1 --> C{Valid Size?}
    C -->|❌ No| D[❌ Reject - Size Too Large]
    C -->|✅ Yes| E[Continue Validation]
    
    B2 --> F{Allowed Extension?}
    F -->|❌ No| G[❌ Reject - Invalid Type]
    F -->|✅ Yes| E
    
    B3 --> H{MIME Match?}
    H -->|❌ No| I[❌ Reject - Type Mismatch]
    H -->|✅ Yes| E
    
    B4 --> J{Signature Valid?}
    J -->|❌ No| K[❌ Reject - Invalid Signature]
    J -->|✅ Yes| E
    
    B5 --> L{Malware Free?}
    L -->|❌ No| M[❌ Reject - Malware Detected]
    L -->|✅ Yes| E
    
    E --> N[✅ All Checks Passed]
    N --> O[🗂️ Store in Secure Directory]
    O --> P[🔐 Generate Download Token]
    P --> Q[📋 Log Upload Activity]
    
    Q --> R[✅ Upload Complete]
    R --> S[📧 Notify User]
    
    D --> T[📧 Error Notification]
    G --> T
    I --> T
    K --> T
    M --> T
```

### 9. 📊 Reporting & Analytics Flow

```mermaid
graph TD
    A[📊 Analytics Dashboard] --> B[📈 Usage Statistics]
    B --> B1[Daily Bookings]
    B --> B2[Monthly Revenue]
    B --> B3[Equipment Utilization]
    B --> B4[User Activity]
    
    A --> C[📄 Report Generation]
    C --> C1[Select Report Type]
    C1 --> C2[Financial Report]
    C1 --> C3[Usage Report]
    C1 --> C4[Equipment Report]
    C1 --> C5[User Report]
    
    C2 --> D[💰 Financial Analysis]
    D --> D1[Revenue by Service]
    D --> D2[Payment Status]
    D --> D3[Outstanding Amounts]
    
    C3 --> E[📋 Usage Analysis]
    E --> E1[Service Popularity]
    E --> E2[Peak Usage Times]
    E --> E3[User Demographics]
    
    C4 --> F[🔧 Equipment Analysis]
    F --> F1[Utilization Rates]
    F --> F2[Maintenance Costs]
    F --> F3[Downtime Analysis]
    
    C5 --> G[👥 User Analysis]
    G --> G1[Registration Trends]
    G --> G2[Activity Patterns]
    G --> G3[Satisfaction Metrics]
    
    D --> H[📊 Generate Charts]
    E --> H
    F --> H
    G --> H
    
    H --> I[📄 Export Options]
    I --> I1[📊 PDF Report]
    I --> I2[📈 Excel File]
    I --> I3[📋 CSV Data]
```

### 10. 📱 Mobile & Responsive Flow

```mermaid
graph TD
    A[📱 Mobile Device] --> B[🌐 Access Website]
    B --> C[📐 Responsive Layout]
    
    C --> D[📋 Mobile Navigation]
    D --> D1[☰ Hamburger Menu]
    D --> D2[🔍 Search Function]
    D --> D3[⚡ Quick Actions]
    
    D1 --> E[📖 Main Menu]
    E --> E1[🏠 Home]
    E --> E2[📋 Booking]
    E --> E3[🔬 Equipment]
    E --> E4[👤 Profile]
    
    D3 --> F[⚡ Quick Booking]
    F --> F1[📝 Simple Form]
    F1 --> F2[📅 Date Picker]
    F2 --> F3[✅ Submit]
    
    C --> G[📱 Touch Optimized]
    G --> G1[👆 Large Buttons]
    G --> G2[📏 Proper Spacing]
    G --> G3[⚡ Fast Loading]
    
    G --> H[🔄 Offline Support]
    H --> H1[💾 Cache Critical Data]
    H --> H2[📊 Sync When Online]
```

## 🎯 Key User Journeys

### Journey 1: New Student Registration → First Booking

```
👤 Student Visitor
    ↓
📝 Register Account (Role: Mahasiswa)
    ↓
✉️ Email Verification
    ↓
🔑 First Login
    ↓
🎓 Student Dashboard
    ↓
📋 Create New Booking
    ↓
🔬 Select GC-MS Analysis
    ↓
📅 Choose Available Slot
    ↓
📎 Upload Sample Files
    ↓
✅ Submit Booking Request
    ↓
📧 Confirmation Email
    ↓
⏳ Wait for Admin Approval
    ↓
✅ Booking Approved
    ↓
🔬 Attend Lab Session
    ↓
📄 Receive Analysis Report
```

### Journey 2: Industry Client Commercial Service

```
🏭 Industry Representative
    ↓
🌐 Browse Services (No Login)
    ↓
💰 Check Commercial Pricing
    ↓
📝 Register Company Account
    ↓
🔑 Login as Industry User
    ↓
📋 Submit Service Request
    ↓
📞 Admin Contact for Quote
    ↓
💰 Negotiate Service Agreement
    ↓
✅ Approve Quote & Schedule
    ↓
🔬 Service Execution
    ↓
📄 Detailed Report Delivery
    ↓
💳 Payment Processing
    ↓
📊 Ongoing Relationship
```

### Journey 3: Admin Daily Workflow

```
⚙️ Admin Login
    ↓
📊 Review Dashboard Statistics
    ↓
📋 Check Pending Bookings (5 new)
    ↓
🔍 Review First Booking Request
    ↓
✅ Approve Standard Request
    ↓
📧 System Sends Approval Email
    ↓
🔍 Review Complex Request
    ↓
📞 Contact User for Clarification
    ↓
✅ Approve After Discussion
    ↓
🔧 Check Equipment Status
    ↓
⚠️ Schedule Maintenance for AAS
    ↓
👥 Review New User Registrations
    ↓
✅ Activate Verified Users
    ↓
📊 Generate Weekly Report
    ↓
🔚 End of Day Summary
```

## 🔄 Integration Points

### External System Integration

```mermaid
graph TD
    A[ILab System] --> B[📧 Email Service]
    A --> C[💳 Payment Gateway]
    A --> D[📊 Analytics Platform]
    A --> E[🔐 Authentication Service]
    
    B --> B1[SMTP Server]
    B --> B2[Email Templates]
    B --> B3[Delivery Tracking]
    
    C --> C1[Payment Processing]
    C --> C2[Invoice Generation]
    C --> C3[Receipt Management]
    
    D --> D1[Usage Analytics]
    D --> D2[Performance Metrics]
    D --> D3[Business Intelligence]
    
    E --> E1[LDAP Integration]
    E --> E2[Single Sign-On]
    E --> E3[Multi-Factor Auth]
```

## 🎨 UI/UX Flow States

### Visual State Management

```mermaid
graph TD
    A[🎨 UI State] --> B[📱 Loading States]
    B --> B1[⏳ Page Loading]
    B --> B2[🔄 Form Submitting]
    B --> B3[📊 Data Loading]
    
    A --> C[✅ Success States]
    C --> C1[✅ Form Submitted]
    C --> C2[💾 Data Saved]
    C --> C3[📧 Email Sent]
    
    A --> D[❌ Error States]
    D --> D1[🚫 Validation Errors]
    D --> D2[⚠️ Server Errors]
    D --> D3[🔌 Connection Issues]
    
    A --> E[📊 Empty States]
    E --> E1[📋 No Bookings]
    E --> E2[🔍 No Search Results]
    E --> E3[📊 No Data Available]
```

---

## 📊 Flow Metrics & KPIs

| Flow | Success Rate Target | Average Time | Drop-off Points |
|------|-------------------|--------------|-----------------|
| **User Registration** | 85%+ | 3 minutes | Email verification |
| **Booking Creation** | 90%+ | 5 minutes | File upload step |
| **Admin Approval** | 95%+ | 2 minutes | Complex requests |
| **Payment Process** | 95%+ | 3 minutes | Payment gateway |
| **Mobile Usage** | 80%+ | Same as desktop | Form complexity |

## 🎯 Optimization Opportunities

### Identified Improvement Areas

1. **📱 Mobile Booking** - Simplify multi-step form
2. **🔍 Search Experience** - Add auto-suggestions
3. **📧 Email Delivery** - Implement retry logic
4. **📊 Dashboard Loading** - Add progressive loading
5. **🔐 Security Flow** - Streamline file validation

---

**🎨 Visual Flow Diagrams Ready!** 

User flows memberikan comprehensive overview dari semua user interactions dan system processes dalam ILab UNMUL, perfect untuk development reference, user training, dan system documentation.
