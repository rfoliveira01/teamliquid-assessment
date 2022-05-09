CREATE TABLE `testtable` (
  `thing_id` int(11) NOT NULL,
  `thing_name` varchar(255) NOT NULL,
  `thing_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `testtable`
  ADD PRIMARY KEY (`thing_id`);

ALTER TABLE `testtable`
  MODIFY `thing_id` int(11) NOT NULL AUTO_INCREMENT;