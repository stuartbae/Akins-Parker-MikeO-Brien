--
-- Database: `musicbox`
--
-- CREATE DATABASE IF NOT EXISTS `swim` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
-- USE `swim`;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE IF NOT EXISTS `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL, -- user_id
  `pool_id` int(11) NOT NULL,
  `tuition` int(11) NOT NULL default 380,
  `deposit` int(11) NOT NULL default 190,
  `approved` tinyint(1) default NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`lesson_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `group_code` varchar(255) NOT NULL,
  `seats` int(11) NOT NULL,
  `starts_at` int(11) NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Table structure for table group student placement
--

CREATE TABLE IF NOT EXISTS `placements` (
  `placement_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`placement_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;
--
-- Table structure for table `addresses`
--

CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `street` varchar(255) NOT NULL,
  `street2` varchar(255),
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  -- `country` varchar(255) NOT NULL  default 'United Sates',
  `billing` tinyint(1) default NULL, -- for user not pool null or 1
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `salt` varchar(23) NOT NULL,
  `password` varchar(88) NOT NULL,
  `firstname` varchar(255),
  `lastname` varchar(255),
  `spouse_firstname` varchar(255),
  `spouse_lastname` varchar(255),
  `mobile` varchar(255),
  `home` varchar(255),
  `email` varchar(255),
  `role` varchar(255),
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;


--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `level_id` int(11) NOT NULL,
  `note` TEXT,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;
-- --------------------------------------------------------

--
-- Table structure for table `pools`
--

CREATE TABLE IF NOT EXISTS `pools` (
  `pool_id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `access_info` TEXT,
  `image` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`pool_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

CREATE TABLE IF NOT EXISTS `exp_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

CREATE TABLE IF NOT EXISTS `coupons` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `expire_at` date NOT NULL,
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6;


INSERT INTO exp_levels (level) values ('Does not swim');
INSERT INTO exp_levels (level) values ('Some swiming, not independent');
INSERT INTO exp_levels (level) values ('Returning student to this program');
INSERT INTO exp_levels (level) values ('Good basic skills, ready for stroke');
INSERT INTO exp_levels (level) values ('Advanced stroke technique');

Insert admin user


INSERT INTO `users` (`user_id`, `username`, `salt`, `password`, `firstname`, `lastname`, `spouse_firstname`, `spouse_lastname`, `mobile`, `home`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '44550680455f35ccf1214b', 'HU1hjxyF5q8OORn2rwicGa4uY41BNBFUtdOwcz1GpgLvXZgcT6gUqOoiqHKQ4/VWBzWJ3eNlSdXfvk0OVsU6cw==', 'Stuart', 'Bae', NULL, NULL, NULL, NULL, 'stu.pae@gmail.com', 'ROLE_ADMIN', 1379889332);
