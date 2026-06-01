# Digital Village Management Dashboard (DVMD)
DVMD is a centralized web-based platform designed to manage emergency response, community incidents, 
and village resources in Malaysia. It connects Citizens, Ketua Kampung, Penghulu, 
District Officers, and KPLB HQ to ensure rapid disaster response and efficient communication.

## Features by Role
### 1. Citizen
   
      Dashboard: Personal safety overview and status of recent submissions.

      SOS Alerts: One-click emergency button to signal immediate distress to authorities.
      
      Report Incident: Submit new reports for floods, fires, or accidents with location data.
      
      Profile: Manage personal contact details and account settings.

### 2.  Ketua Kampung / Penghulu (Village Head)
   
      Dashboard: Real-time view of active SOS signals and incident counts in the village.
      
      Incident Response: Monitor ongoing situations and mobilize village assets.
      
      Manage Reports: Queue to verify, approve, or reject reports submitted by residents.
      
      Emergency Alerts: Send broadcast announcements specifically to village residents.

### 3.  District Officer
   
      Dashboard: Statistical overview of all villages within the district.
      
      District Incidents: Monitor resolution progress across multiple villages.
      
      Report & Alert: Issue district-level warnings and coordinate communication.
      
      User Management: Register and manage Citizens and Village Head (Ketua Kampung/Penghulu) accounts.

### 4.  HQ (KPLB National Admin)
   
      HQ Dashboard: High-level analytics, heatmaps, and national statistics.
      
      National Incidents: Full GIS visualization of all active and resolved cases nationwide.
      
      Broadcast Center: Publish system-wide alerts, weather warnings, and policy updates.
      
      System Audit Logs: Track security logs, login activity, and administrative actions.
      
      Users Management: Register and manage District Officers account.

## Installation and Setup
1. **Clone/Download:** Place the project folder dvmd_system inside your htdocs directory:
   ###
       C:\xampp\htdocs\dvmd_system\

3. **Database Setup:**
    ###
        Open phpMyAdmin (http://localhost/phpmyadmin).
        
        Create a new database named ___dvmd_db___.
        
        Import the SQL schema in ___database_schema___ folder.

4. **Run app:**
   ###
       Start Apache and MySQL in XAMPP.
       Open your browser and visit: http://localhost/dvmd_system/login.php
