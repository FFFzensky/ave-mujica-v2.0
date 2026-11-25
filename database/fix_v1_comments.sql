-- OPSIONAL: hanya kalau kamu memindahkan komentar dari v1.
-- v1 memakai real_escape_string + prepared statement sekaligus, jadi komentar seperti  it's
-- tersimpan sebagai  it\'s . Query ini membersihkan backslash ganda tersebut.
-- Cek dulu hasilnya:  SELECT id, comment FROM comments WHERE comment LIKE '%\\%';
UPDATE comments SET comment = REPLACE(REPLACE(REPLACE(comment, "\'", "'"), '\\"', '"'), '\\', '\');
