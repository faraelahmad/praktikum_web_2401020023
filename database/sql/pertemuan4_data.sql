-- Pertemuan 4: DML Program Studi dan Mahasiswa
USE praktikum_web;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Informatika'),
    ('Sistem Informasi');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2201010001', 'Andi Saputra',
     'andi@example.com', 20, 1),
    ('2201010002', 'Siti Rahma',
     'siti@example.com', 19, 1),
    ('2201020001', 'Budi Pratama',
     'budi@example.com', 21, 2),
    ('2201020099', 'Data Sementara',
     'sementara@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'andi.saputra@example.com'
WHERE nim = '2201010001';

DELETE FROM mahasiswa
WHERE nim = '2201020099';

SELECT m.nim, m.nama, m.email, m.usia,
       p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;
