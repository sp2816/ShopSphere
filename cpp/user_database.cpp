#include "user_database.h"
#include <iostream>

void UserDatabase::addUser(const User& user) {
    users.push_back(user);
}

User* UserDatabase::findUserById(int id) {
    for (auto& user : users) {
        if (user.id == id) return &user;
    }
    return nullptr;
}

bool UserDatabase::updateUser(int id, const User& updatedUser) {
    for (auto& user : users) {
        if (user.id == id) {
            user = updatedUser;
            return true;
        }
    }
    return false;
}

bool UserDatabase::deleteUser(int id) {
    for (auto it = users.begin(); it != users.end(); ++it) {
        if (it->id == id) {
            users.erase(it);
            return true;
        }
    }
    return false;
}

void UserDatabase::listUsers() const {
    for (const auto& user : users) {
        std::cout << "ID: " << user.id << ", Name: " << user.name << ", Email: " << user.email << std::endl;
    }
}
