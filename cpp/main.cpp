#include "user_database.h"
#include <iostream>

int main() {
    UserDatabase db;
    db.addUser({1, "Alice", "alice@example.com", "pass123"});
    db.addUser({2, "Bob", "bob@example.com", "bobpass"});
    db.listUsers();

    User* user = db.findUserById(1);
    if (user) {
        std::cout << "Found user: " << user->name << std::endl;
    }

    db.updateUser(2, {2, "Bob Updated", "bob@newmail.com", "newpass"});
    db.listUsers();

    db.deleteUser(1);
    db.listUsers();
    return 0;
}
