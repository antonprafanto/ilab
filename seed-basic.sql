-- Basic seed data for ILab UNMUL
USE ilab_unmul;

-- Insert roles with manual UUIDs
INSERT INTO roles (id, name, display_name, description, permissions, level) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'admin', 'Administrator', 'Full system administrator access', 
 '["users:read", "users:write", "users:delete", "equipment:read", "equipment:write", "equipment:delete", "bookings:read", "bookings:write", "bookings:delete", "bookings:approve", "samples:read", "samples:write", "samples:delete", "payments:read", "payments:write", "payments:delete", "reports:read", "system:manage"]', 8),

('550e8400-e29b-41d4-a716-446655440002', 'director', 'Direktur', 'Laboratory director access', 
 '["users:read", "users:write", "equipment:read", "equipment:write", "bookings:read", "bookings:write", "bookings:approve", "samples:read", "samples:write", "payments:read", "payments:write", "reports:read"]', 7),

('550e8400-e29b-41d4-a716-446655440003', 'lab_head', 'Kepala Laboratorium', 'Laboratory head access', 
 '["equipment:read", "equipment:write", "bookings:read", "bookings:write", "bookings:approve", "samples:read", "samples:write", "payments:read", "reports:read"]', 5),

('550e8400-e29b-41d4-a716-446655440004', 'laboran', 'Laboran', 'Laboratory technician access', 
 '["equipment:read", "bookings:read", "bookings:write", "samples:read", "samples:write", "payments:read"]', 4),

('550e8400-e29b-41d4-a716-446655440005', 'lecturer', 'Dosen', 'Faculty member access', 
 '["equipment:read", "bookings:read", "bookings:write", "samples:read", "samples:write", "payments:read"]', 3),

('550e8400-e29b-41d4-a716-446655440006', 'student', 'Mahasiswa', 'Student access', 
 '["equipment:read", "bookings:read", "bookings:write", "samples:read", "payments:read"]', 2),

('550e8400-e29b-41d4-a716-446655440007', 'external', 'Eksternal', 'External user access', 
 '["equipment:read", "bookings:read", "bookings:write", "samples:read", "payments:read"]', 1);

-- Insert equipment categories
INSERT INTO equipment_categories (id, name, description, icon) VALUES
('650e8400-e29b-41d4-a716-446655440001', 'Analytical Chemistry', 'Peralatan untuk analisis kimia', 'beaker'),
('650e8400-e29b-41d4-a716-446655440002', 'Molecular Biology', 'Peralatan untuk biologi molekuler', 'dna'),
('650e8400-e29b-41d4-a716-446655440003', 'Sample Preparation', 'Peralatan preparasi sampel', 'test-tube'),
('650e8400-e29b-41d4-a716-446655440004', 'Microscopy', 'Peralatan mikroskopi', 'microscope'),
('650e8400-e29b-41d4-a716-446655440005', 'General Equipment', 'Peralatan umum laboratorium', 'cog');