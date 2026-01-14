-- მონაცემთა ბაზის შექმნა
CREATE DATABASE fitness_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_app;

-- 1. მომხმარებლების ცხრილი
-- აქ ინახება ყველა რეგისტრირებული მომხმარებელი
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- დაშიფრული პაროლი
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. კატეგორიების ცხრილი
-- მაგ: კარდიო, ძალოვნი, იოგა და ა.შ
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(100) -- სურათის სახელი
);

-- 3. ინსტრუქტორების ცხრილი
-- ვინც ასწავლის ვარჯიშებს
CREATE TABLE instructors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    bio TEXT,
    photo VARCHAR(255),
    specialization VARCHAR(100),
    experience_years INT
);

-- 4. ვარჯიშების ცხრილი (მთავარი ცხრილი)
-- აქ ინახება თითოეული workout პროგრამა
CREATE TABLE workouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration INT, -- წუთებში
    image VARCHAR(255),
    instructor_id INT,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 5. ვარჯიშის ელემენტების ცხრილი
-- თითოეული workout-ის კონკრეტული სავარჯიშოები
CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workout_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sets INT, -- რამდენი სერია
    reps INT, -- რამდენი გამეორება
    description TEXT,
    video_url VARCHAR(255),
    order_number INT, -- რიგითობა
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE
);

-- 6. მომხმარებლის პროგრესის ცხრილი
-- აქ ფიქსირდება რა გაიარა მომხმარებელმა
CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    workout_id INT NOT NULL,
    completed_date DATE,
    notes TEXT, -- შენიშვნები
    rating INT CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE
);

-- 7. შეფასებების ცხრილი
-- მომხმარებლები აფასებენ ვარჯიშებს
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    workout_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE
);

-- საწყისი მონაცემების ჩასმა (დემო მონაცემები)

-- ადმინის შექმნა (პაროლი: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@fitness.ge', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- კატეგორიების დამატება
INSERT INTO categories (name, description) VALUES 
('კარდიო', 'გულ-სისხლძარღვთა სისტემის გასაძლიერებლად'),
('ძალოვნი', 'კუნთების მასის ზრდისთვის'),
('იოგა', 'მოქნილობისა და რელაქსაციისთვის'),
('HIIT', 'მაღალინტენსიური ინტერვალური ვარჯიში'),
('სტრეჩინგი', 'გაჭიმვის ვარჯიშები');

-- ინსტრუქტორების დამატება
INSERT INTO instructors (name, bio, specialization, experience_years) VALUES 
('გიორგი ბერიძე', 'სერტიფიცირებული ფიტნეს ტრენერი 10 წლიანი გამოცდილებით', 'ძალოვნი ვარჯიშები', 10),
('ნინო მელაძე', 'იოგა და სტრეჩინგის სპეციალისტი', 'იოგა', 8),
('დავით ლომიძე', 'HIIT და კარდიო ტრენერი', 'კარდიო/HIIT', 6);