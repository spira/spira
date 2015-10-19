CREATE TABLE auth_assignment
(
    item_name VARCHAR(128) NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    created_at TIMESTAMP(0) NOT NULL,
    PRIMARY KEY (item_name, user_id)
);
CREATE TABLE auth_item
(
    name VARCHAR(128) PRIMARY KEY NOT NULL,
    type VARCHAR(255) NOT NULL,
    description VARCHAR,
    rule_name VARCHAR(128),
    created_at TIMESTAMP(0) NOT NULL,
    updated_at TIMESTAMP(0)
);
CREATE TABLE auth_item_child
(
    parent VARCHAR(128) NOT NULL,
    child VARCHAR(128) NOT NULL,
    PRIMARY KEY (parent, child)
);
CREATE INDEX auth_item_type_index ON auth_item (type);
ALTER TABLE auth_item_child ADD FOREIGN KEY (child) REFERENCES auth_item (name) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE auth_item_child ADD FOREIGN KEY (parent) REFERENCES auth_item (name) ON DELETE CASCADE ON UPDATE CASCADE;
