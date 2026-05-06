#ifndef USER_DATABASE_H
#define USER_DATABASE_H
#include <string>
#include <vector>

struct User {
    int id;
    std::string name;
    std::string email;
    std::string password;
};

class UserDatabase {
private:
    std::vector<User> users;
public:
    void addUser(const User& user);
    User* findUserById(int id);
    bool updateUser(int id, const User& updatedUser);
    bool deleteUser(int id);
    void listUsers() const;
};

#endif // USER_DATABASE_H
