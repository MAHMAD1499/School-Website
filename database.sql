CREATE DATABASE IF NOT EXISTS `ksm_database`;
USE `ksm_database`;

-- 1. users_students
CREATE TABLE IF NOT EXISTS `users_students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `phone` VARCHAR(50),
  `address` TEXT,
  `password` VARCHAR(255) NOT NULL,
  `class` VARCHAR(100),
  `rollNo` VARCHAR(50),
  `parentName` VARCHAR(255),
  `profilePic` VARCHAR(500)
);

INSERT IGNORE INTO `users_students` (`id`, `name`, `email`, `phone`, `address`, `password`, `class`, `rollNo`, `parentName`) VALUES
(1, 'Ali Hassan', 'ali@student.ksm', '+92 300 1234567', '123 Main St, Karachi', 'student123', 'Kindergarten A', 'KA-001', 'Mr. Hassan Ali'),
(2, 'Zara Ahmed', 'zara@student.ksm', '+92 321 7654321', '456 Elm St, Lahore', 'student123', 'Early Childhood B', 'ECB-002', 'Mrs. Sana Ahmed'),
(3, 'Ibrahim Khan', 'ibrahim@student.ksm', '+92 333 9876543', '789 Oak Ave, Haripur', 'student123', 'Kindergarten A', 'KA-003', 'Mr. Imran Khan'),
(4, 'Fatima Noor', 'fatima@student.ksm', '+92 345 1112233', '12 Pine Rd, Haripur', 'student123', 'Junior Level', 'JL-001', 'Mr. Noor Ahmed'),
(5, 'Hamza Rauf', 'hamza@student.ksm', '+92 312 4445566', '56 Cedar Ln, Haripur', 'student123', 'Early Childhood B', 'ECB-003', 'Mr. Abdul Rauf');


-- 2. users_staff
CREATE TABLE IF NOT EXISTS `users_staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `phone` VARCHAR(50),
  `password` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255),
  `class` VARCHAR(100),
  `bio` TEXT,
  `emoji` VARCHAR(50),
  `profilePic` VARCHAR(500)
);

INSERT IGNORE INTO `users_staff` (`id`, `name`, `email`, `phone`, `password`, `subject`, `class`, `bio`, `emoji`) VALUES
(1, 'Ms. Ayesha Raza', 'ayesha@staff.ksm', '+92 300 1111111', 'staff123', 'Language & Literacy', 'Kindergarten A', 'Specializes in early childhood language development.', '👩‍🏫'),
(2, 'Mr. Bilal Ahmed', 'bilal@staff.ksm', '+92 300 2222222', 'staff123', 'Mathematics & Science', 'Early Childhood B', 'Passionate about making math fun for young learners.', '👨‍🏫'),
(3, 'Ms. Fatima Malik', 'fatima@staff.ksm', '+92 300 3333333', 'staff123', 'Art & Creativity', 'Junior Level', 'Art enthusiast promoting creative expression in children.', '👩‍🎨');


-- 3. teachers
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `role` VARCHAR(255),
  `subject` VARCHAR(255),
  `emoji` VARCHAR(50),
  `bio` TEXT
);

INSERT IGNORE INTO `teachers` (`id`, `name`, `role`, `subject`, `emoji`, `bio`) VALUES
(1, 'Ms. Saadia Khan', 'Principal', 'Administration', '👩‍💼', 'Certified Montessori educator with 15+ years of experience.'),
(2, 'Ms. Ayesha Raza', 'Junior/senior teacher', 'Language & Literacy', '👩‍🏫', 'Specializes in early childhood language development.'),
(3, 'Mr. Bilal Ahmed', 'Science teacher', 'Mathematics & Science', '👨‍🏫', 'Passionate about making math fun for young learners.'),
(4, 'Ms. Fatima Malik', 'Montessori teacher', 'Art & Creativity', '👩‍🎨', 'Art enthusiast promoting creative expression in children.'),
(5, 'Ms. Hira Yousuf', 'P.E teacher\'s', 'Physical Education', '🏃‍♀️', 'Focused on gross motor development and healthy habits.'),
(6, 'Mr. Usman Tariq', 'Computer teacher', 'General Support', '👨‍🎓', 'Dedicated assistant supporting classroom activities.');


-- 4. gallery
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `url` VARCHAR(500) NOT NULL,
  `caption` VARCHAR(255)
);

INSERT IGNORE INTO `gallery` (`id`, `url`, `caption`) VALUES
(1, 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600', 'Children in the classroom'),
(2, 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=600', 'Art and crafts session'),
(3, 'https://images.unsplash.com/photo-1551649001-7a2482d98d05?w=600', 'Outdoor play time'),
(4, 'https://images.unsplash.com/photo-1567168539906-f3ae74aa64ec?w=600', 'Reading circle time'),
(5, 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=600', 'Science experiments'),
(6, 'https://images.unsplash.com/photo-1534050359320-02900022671e?w=600', 'Annual sports day');


-- 5. news
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `date` DATE,
  `category` VARCHAR(100)
);

INSERT IGNORE INTO `news` (`id`, `title`, `body`, `date`, `category`) VALUES
(1, 'New Academic Year 2026-27 Begins!', 'We are thrilled to welcome all students back for an exciting new academic year. Classes begin September 15, 2026. New uniforms and stationery packs are available from the school office.', '2026-09-01', 'General'),
(2, 'Montessori Certification Achieved', 'Kindergarten Saadia\'s Montessori School has received renewed AMI (Association Montessori Internationale) certification for academic excellence.', '2026-08-20', 'Achievement'),
(3, 'Parent-Teacher Meeting Scheduled', 'The quarterly parent-teacher meeting is scheduled for September 25, 2026. Parents are requested to register their slots via the school office or admission desk.', '2026-08-10', 'Event');


-- 6. events
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `date` DATE NOT NULL,
  `time` VARCHAR(50),
  `location` VARCHAR(255),
  `description` TEXT,
  `category` VARCHAR(100)
);

INSERT IGNORE INTO `events` (`id`, `title`, `date`, `time`, `location`, `description`, `category`) VALUES
(1, 'Annual Sports Day', '2026-10-15', '8:00 AM', 'School Grounds', 'Join us for our exciting annual sports day with races, fun games, and prizes for all age groups.', 'Sports'),
(2, 'Science & Art Exhibition', '2026-11-05', '10:00 AM', 'Main Hall', 'Students showcase their science projects and art portfolios to parents and guests.', 'Academic'),
(3, 'Parents Orientation Day', '2026-09-20', '9:00 AM', 'Assembly Hall', 'Orientation session for parents of new admissions. Curriculum overview and Q&A.', 'Meeting'),
(4, 'Eid Celebration Event', '2026-12-10', '11:00 AM', 'Main Hall', 'A festive celebration with performances, food stalls, and fun activities for the whole family.', 'Cultural');


-- 7. admissions
CREATE TABLE IF NOT EXISTS `admissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `child_name` VARCHAR(255) NOT NULL,
  `dob` DATE,
  `parent_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255),
  `address` TEXT,
  `prior_school` VARCHAR(255),
  `class_applied` VARCHAR(100),
  `message` TEXT,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `submittedAt` DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- 8. contacts
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255),
  `message` TEXT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Unread',
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- 9. homework
CREATE TABLE IF NOT EXISTS `homework` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `class_name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `due_date` DATE,
  `date_assigned` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `staff_id` INT,
  FOREIGN KEY (`staff_id`) REFERENCES `users_staff`(`id`)
);


-- 10. attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `notes` TEXT,
  FOREIGN KEY (`student_id`) REFERENCES `users_students`(`id`)
);
