-- Create the database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'streeties')
BEGIN
    CREATE DATABASE streeties;
END
GO

USE streeties;
GO

-- Create users table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[users]') AND type in (N'U'))
BEGIN
    CREATE TABLE users (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(100) NOT NULL,
        email NVARCHAR(100) NOT NULL UNIQUE,
        phone NVARCHAR(20) NOT NULL,
        password NVARCHAR(255) NOT NULL,
        created_at DATETIME2 DEFAULT GETDATE(),
        updated_at DATETIME2 DEFAULT GETDATE()
    );
END
GO

-- Create pets table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[pets]') AND type in (N'U'))
BEGIN
    CREATE TABLE pets (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(100) NOT NULL,
        type NVARCHAR(50) NOT NULL,
        breed NVARCHAR(100) NOT NULL,
        age NVARCHAR(50) NOT NULL,
        gender NVARCHAR(20) NOT NULL,
        size NVARCHAR(20) NOT NULL,
        description NVARCHAR(MAX),
        image_url NVARCHAR(255),
        status NVARCHAR(20) DEFAULT 'available' CHECK (status IN ('available', 'adopted', 'pending')),
        created_at DATETIME2 DEFAULT GETDATE(),
        updated_at DATETIME2 DEFAULT GETDATE()
    );
END
GO

-- Create user_preferences table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[user_preferences]') AND type in (N'U'))
BEGIN
    CREATE TABLE user_preferences (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        preferred_pet_type NVARCHAR(50),
        preferred_age_range NVARCHAR(50),
        notifications_enabled BIT DEFAULT 1,
        created_at DATETIME2 DEFAULT GETDATE(),
        updated_at DATETIME2 DEFAULT GETDATE(),
        CONSTRAINT FK_UserPreferences_Users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
END
GO

-- Create adoption_applications table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[adoption_applications]') AND type in (N'U'))
BEGIN
    CREATE TABLE adoption_applications (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        pet_id INT NOT NULL,
        status NVARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
        application_date DATETIME2 DEFAULT GETDATE(),
        home_type NVARCHAR(50),
        has_yard BIT,
        other_pets NVARCHAR(MAX),
        experience NVARCHAR(MAX),
        reason_for_adoption NVARCHAR(MAX),
        created_at DATETIME2 DEFAULT GETDATE(),
        updated_at DATETIME2 DEFAULT GETDATE(),
        CONSTRAINT FK_AdoptionApplications_Users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT FK_AdoptionApplications_Pets FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
    );
END
GO

-- Create user_favorites table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[user_favorites]') AND type in (N'U'))
BEGIN
    CREATE TABLE user_favorites (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        pet_id INT NOT NULL,
        created_at DATETIME2 DEFAULT GETDATE(),
        CONSTRAINT FK_UserFavorites_Users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT FK_UserFavorites_Pets FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
    );
END
GO

-- Create indexes
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_user_email' AND object_id = OBJECT_ID('users'))
    CREATE INDEX idx_user_email ON users(email);
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_application_status' AND object_id = OBJECT_ID('adoption_applications'))
    CREATE INDEX idx_application_status ON adoption_applications(status);
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_user_favorites' AND object_id = OBJECT_ID('user_favorites'))
    CREATE INDEX idx_user_favorites ON user_favorites(user_id, pet_id);
GO

-- Insert sample pets data
IF NOT EXISTS (SELECT TOP 1 1 FROM pets)
BEGIN
    INSERT INTO pets (name, type, breed, age, gender, size, description, image_url, status)
    VALUES 
    ('Bruno', 'dog', 'Indian Street Dog', '1.5 years', 'Male', 'Medium', 'Friendly and energetic, loves to play with everyone!', 'pics/dog1.jpg', 'available'),
    ('Sona', 'cat', 'Indian Short Hair', '2 years', 'Female', 'Small', 'Graceful and elegant, enjoys peaceful window watching!', 'pics/cat2.jpg', 'available'),
    ('Rani', 'cat', 'Indian Short Hair', '1 year', 'Female', 'Small', 'Playful and sweet, brings joy to everyone around her!', 'pics/cat3.jpg', 'available');
END
GO 