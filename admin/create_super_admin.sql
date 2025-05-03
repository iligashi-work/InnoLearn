-- Create the first super admin account
INSERT INTO admins (username, password, role, is_active) 
VALUES ('superadmin', 'admin123', 'super_admin', 1); 