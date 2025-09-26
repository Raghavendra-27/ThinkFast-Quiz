<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

session_start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_categories':
        getCategories();
        break;
    case 'get_questions':
        getQuestions($pdo);
        break;
    case 'save_result':
        saveQuizResult($pdo);
        break;
    case 'get_user_stats':
        getUserStats($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getCategories() {
    $categories = [
        [
            'name' => 'General Knowledge',
            'icon' => 'bx-world',
            'color' => 'blue',
            'questionCount' => 15
        ],
        [
            'name' => 'Science & Technology',
            'icon' => 'bx-atom',
            'color' => 'green',
            'questionCount' => 15
        ],
        [
            'name' => 'Sports',
            'icon' => 'bx-football',
            'color' => 'orange',
            'questionCount' => 15
        ],
        [
            'name' => 'History',
            'icon' => 'bx-history',
            'color' => 'purple',
            'questionCount' => 15
        ],
        [
            'name' => 'Entertainment',
            'icon' => 'bx-movie',
            'color' => 'pink',
            'questionCount' => 15
        ]
    ];
    
    echo json_encode(['success' => true, 'categories' => $categories]);
}

function getQuestions($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }

    $category = $_GET['category'] ?? '';
    
    if (empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Category is required']);
        return;
    }

    // Quiz questions data
    $questionsData = [
        'General Knowledge' => [
            [
                'question' => 'What is the capital of India?',
                'correct_answer' => 'Delhi',
                'incorrect_answers' => ['Mumbai', 'Kolkata', 'Gujarat']
            ],
            [
                'question' => 'Which planet is known as the Red Planet?',
                'correct_answer' => 'Mars',
                'incorrect_answers' => ['Earth', 'Venus', 'Jupiter']
            ],
            [
                'question' => 'What is the largest ocean on Earth?',
                'correct_answer' => 'Pacific Ocean',
                'incorrect_answers' => ['Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean']
            ],
            [
                'question' => 'Who painted the Mona Lisa?',
                'correct_answer' => 'Leonardo da Vinci',
                'incorrect_answers' => ['Vincent van Gogh', 'Pablo Picasso', 'Michelangelo']
            ],
            [
                'question' => 'What is the smallest country in the world?',
                'correct_answer' => 'Vatican City',
                'incorrect_answers' => ['Monaco', 'Liechtenstein', 'San Marino']
            ],
            [
                'question' => 'Which gas makes up most of Earth\'s atmosphere?',
                'correct_answer' => 'Nitrogen',
                'incorrect_answers' => ['Oxygen', 'Carbon Dioxide', 'Argon']
            ],
            [
                'question' => 'What is the longest river in the world?',
                'correct_answer' => 'Nile',
                'incorrect_answers' => ['Amazon', 'Mississippi', 'Yangtze']
            ],
            [
                'question' => 'Which country has the most natural lakes?',
                'correct_answer' => 'Canada',
                'incorrect_answers' => ['Russia', 'Finland', 'United States']
            ],
            [
                'question' => 'What is the fastest land animal?',
                'correct_answer' => 'Cheetah',
                'incorrect_answers' => ['Lion', 'Leopard', 'Tiger']
            ],
            [
                'question' => 'Which element has the chemical symbol \'Au\'?',
                'correct_answer' => 'Gold',
                'incorrect_answers' => ['Silver', 'Aluminum', 'Copper']
            ],
            [
                'question' => 'What is the largest mammal in the world?',
                'correct_answer' => 'Blue Whale',
                'incorrect_answers' => ['African Elephant', 'Giraffe', 'Hippopotamus']
            ],
            [
                'question' => 'Which continent is known as the \'Dark Continent\'?',
                'correct_answer' => 'Africa',
                'incorrect_answers' => ['Asia', 'South America', 'Australia']
            ],
            [
                'question' => 'What is the currency of Japan?',
                'correct_answer' => 'Yen',
                'incorrect_answers' => ['Won', 'Yuan', 'Baht']
            ],
            [
                'question' => 'Which mountain is the highest in the world?',
                'correct_answer' => 'Mount Everest',
                'incorrect_answers' => ['K2', 'Kangchenjunga', 'Lhotse']
            ],
            [
                'question' => 'What is the largest desert in the world?',
                'correct_answer' => 'Antarctic Desert',
                'incorrect_answers' => ['Sahara Desert', 'Arabian Desert', 'Gobi Desert']
            ]
        ],
        'Science & Technology' => [
            [
                'question' => 'Which programming language is known as the Mother of all languages?',
                'correct_answer' => 'C',
                'incorrect_answers' => ['Python', 'Java', 'Assembly']
            ],
            [
                'question' => 'Which of the following is not an operating system?',
                'correct_answer' => 'Python',
                'incorrect_answers' => ['Windows', 'Linux', 'macOS']
            ],
            [
                'question' => 'What does CPU stand for?',
                'correct_answer' => 'Central Processing Unit',
                'incorrect_answers' => ['Computer Processing Unit', 'Central Program Unit', 'Computer Program Unit']
            ],
            [
                'question' => 'Which company developed the Android operating system?',
                'correct_answer' => 'Google',
                'incorrect_answers' => ['Apple', 'Microsoft', 'Samsung']
            ],
            [
                'question' => 'What is the speed of light in vacuum?',
                'correct_answer' => '299,792,458 m/s',
                'incorrect_answers' => ['300,000,000 m/s', '299,000,000 m/s', '301,000,000 m/s']
            ],
            [
                'question' => 'Which element has the atomic number 1?',
                'correct_answer' => 'Hydrogen',
                'incorrect_answers' => ['Helium', 'Lithium', 'Carbon']
            ],
            [
                'question' => 'What is the chemical formula for water?',
                'correct_answer' => 'H2O',
                'incorrect_answers' => ['H2O2', 'CO2', 'NaCl']
            ],
            [
                'question' => 'Which scientist developed the theory of relativity?',
                'correct_answer' => 'Albert Einstein',
                'incorrect_answers' => ['Isaac Newton', 'Galileo Galilei', 'Stephen Hawking']
            ],
            [
                'question' => 'What does HTML stand for?',
                'correct_answer' => 'HyperText Markup Language',
                'incorrect_answers' => ['High Tech Modern Language', 'HyperText Modern Language', 'High Tech Markup Language']
            ],
            [
                'question' => 'Which planet is closest to the Sun?',
                'correct_answer' => 'Mercury',
                'incorrect_answers' => ['Venus', 'Earth', 'Mars']
            ],
            [
                'question' => 'What is the largest planet in our solar system?',
                'correct_answer' => 'Jupiter',
                'incorrect_answers' => ['Saturn', 'Neptune', 'Uranus']
            ],
            [
                'question' => 'Which programming paradigm does JavaScript primarily follow?',
                'correct_answer' => 'Multi-paradigm',
                'incorrect_answers' => ['Object-oriented only', 'Functional only', 'Procedural only']
            ],
            [
                'question' => 'What does API stand for?',
                'correct_answer' => 'Application Programming Interface',
                'incorrect_answers' => ['Application Process Interface', 'Advanced Programming Interface', 'Application Protocol Interface']
            ],
            [
                'question' => 'Which data structure follows LIFO principle?',
                'correct_answer' => 'Stack',
                'incorrect_answers' => ['Queue', 'Array', 'Linked List']
            ],
            [
                'question' => 'What is the primary purpose of a firewall?',
                'correct_answer' => 'Network Security',
                'incorrect_answers' => ['Data Storage', 'Speed Enhancement', 'User Interface']
            ]
        ],
        'Sports' => [
            [
                'question' => 'What is the most popular sport in the world?',
                'correct_answer' => 'Football (Soccer)',
                'incorrect_answers' => ['Cricket', 'Basketball', 'Tennis']
            ],
            [
                'question' => 'Which Indian state is known as the Land of Five Rivers?',
                'correct_answer' => 'Punjab',
                'incorrect_answers' => ['Rajasthan', 'Gujarat', 'Assam']
            ],
            [
                'question' => 'Who was the first Indian to win an individual Olympic gold medal?',
                'correct_answer' => 'Abhinav Bindra',
                'incorrect_answers' => ['Milkha Singh', 'P.T. Usha', 'Sachin Tendulkar']
            ],
            [
                'question' => 'The famous cricket tournament IPL was started in which year?',
                'correct_answer' => '2008',
                'incorrect_answers' => ['2005', '2010', '2007']
            ],
            [
                'question' => 'How many players are on a basketball team on the court?',
                'correct_answer' => '5',
                'incorrect_answers' => ['6', '7', '4']
            ],
            [
                'question' => 'Which sport is played at Wimbledon?',
                'correct_answer' => 'Tennis',
                'incorrect_answers' => ['Cricket', 'Golf', 'Badminton']
            ],
            [
                'question' => 'What is the duration of a football match?',
                'correct_answer' => '90 minutes',
                'incorrect_answers' => ['80 minutes', '100 minutes', '120 minutes']
            ],
            [
                'question' => 'Which country won the FIFA World Cup in 2018?',
                'correct_answer' => 'France',
                'incorrect_answers' => ['Germany', 'Brazil', 'Spain']
            ],
            [
                'question' => 'In which sport would you perform a slam dunk?',
                'correct_answer' => 'Basketball',
                'incorrect_answers' => ['Volleyball', 'Tennis', 'Badminton']
            ],
            [
                'question' => 'How many rings are in the Olympic symbol?',
                'correct_answer' => '5',
                'incorrect_answers' => ['4', '6', '7']
            ],
            [
                'question' => 'Which sport uses a shuttlecock?',
                'correct_answer' => 'Badminton',
                'incorrect_answers' => ['Tennis', 'Squash', 'Table Tennis']
            ],
            [
                'question' => 'What is the maximum score in a single frame of snooker?',
                'correct_answer' => '147',
                'incorrect_answers' => ['100', '150', '200']
            ],
            [
                'question' => 'Which sport is known as \'the beautiful game\'?',
                'correct_answer' => 'Football (Soccer)',
                'incorrect_answers' => ['Basketball', 'Tennis', 'Cricket']
            ],
            [
                'question' => 'How many players are on a cricket team?',
                'correct_answer' => '11',
                'incorrect_answers' => ['10', '12', '9']
            ],
            [
                'question' => 'Which sport is played on ice with sticks and a puck?',
                'correct_answer' => 'Ice Hockey',
                'incorrect_answers' => ['Field Hockey', 'Lacrosse', 'Polo']
            ]
        ],
        'History' => [
            [
                'question' => 'Where is the Colosseum located?',
                'correct_answer' => 'Italy',
                'incorrect_answers' => ['Germany', 'USA', 'Africa']
            ],
            [
                'question' => 'Who was the first person to walk on the moon?',
                'correct_answer' => 'Neil Armstrong',
                'incorrect_answers' => ['Buzz Aldrin', 'John Glenn', 'Yuri Gagarin']
            ],
            [
                'question' => 'In which year did World War II end?',
                'correct_answer' => '1945',
                'incorrect_answers' => ['1944', '1946', '1943']
            ],
            [
                'question' => 'Who was the first President of the United States?',
                'correct_answer' => 'George Washington',
                'incorrect_answers' => ['Thomas Jefferson', 'John Adams', 'Benjamin Franklin']
            ],
            [
                'question' => 'Which ancient wonder of the world was located in Egypt?',
                'correct_answer' => 'Great Pyramid of Giza',
                'incorrect_answers' => ['Hanging Gardens', 'Colossus of Rhodes', 'Lighthouse of Alexandria']
            ],
            [
                'question' => 'Who painted the ceiling of the Sistine Chapel?',
                'correct_answer' => 'Michelangelo',
                'incorrect_answers' => ['Leonardo da Vinci', 'Raphael', 'Donatello']
            ],
            [
                'question' => 'Which empire was ruled by Julius Caesar?',
                'correct_answer' => 'Roman Empire',
                'incorrect_answers' => ['Greek Empire', 'Persian Empire', 'Egyptian Empire']
            ],
            [
                'question' => 'In which year did the Berlin Wall fall?',
                'correct_answer' => '1989',
                'incorrect_answers' => ['1988', '1990', '1991']
            ],
            [
                'question' => 'Who was known as the \'Iron Lady\'?',
                'correct_answer' => 'Margaret Thatcher',
                'incorrect_answers' => ['Indira Gandhi', 'Golda Meir', 'Angela Merkel']
            ],
            [
                'question' => 'Which war was fought between 1950-1953?',
                'correct_answer' => 'Korean War',
                'incorrect_answers' => ['Vietnam War', 'World War II', 'Cold War']
            ],
            [
                'question' => 'Who was the last Emperor of China?',
                'correct_answer' => 'Puyi',
                'incorrect_answers' => ['Qianlong', 'Kangxi', 'Yongzheng']
            ],
            [
                'question' => 'Which ancient city was destroyed by the eruption of Mount Vesuvius?',
                'correct_answer' => 'Pompeii',
                'incorrect_answers' => ['Herculaneum', 'Rome', 'Athens']
            ],
            [
                'question' => 'Who was the first woman to fly solo across the Atlantic?',
                'correct_answer' => 'Amelia Earhart',
                'incorrect_answers' => ['Bessie Coleman', 'Harriet Quimby', 'Jacqueline Cochran']
            ],
            [
                'question' => 'Which dynasty ruled China for over 400 years?',
                'correct_answer' => 'Han Dynasty',
                'incorrect_answers' => ['Tang Dynasty', 'Ming Dynasty', 'Qing Dynasty']
            ],
            [
                'question' => 'Who was the leader of the Soviet Union during World War II?',
                'correct_answer' => 'Joseph Stalin',
                'incorrect_answers' => ['Vladimir Lenin', 'Nikita Khrushchev', 'Leonid Brezhnev']
            ]
        ],
        'Entertainment' => [
            [
                'question' => 'Which movie won the Academy Award for Best Picture in 2020?',
                'correct_answer' => 'Parasite',
                'incorrect_answers' => ['1917', 'Joker', 'Once Upon a Time in Hollywood']
            ],
            [
                'question' => 'Who played the role of Jack in Titanic?',
                'correct_answer' => 'Leonardo DiCaprio',
                'incorrect_answers' => ['Brad Pitt', 'Tom Cruise', 'Johnny Depp']
            ],
            [
                'question' => 'Which streaming platform created the series \'Stranger Things\'?',
                'correct_answer' => 'Netflix',
                'incorrect_answers' => ['Amazon Prime', 'Disney+', 'HBO Max']
            ],
            [
                'question' => 'Who composed the music for the movie \'Interstellar\'?',
                'correct_answer' => 'Hans Zimmer',
                'incorrect_answers' => ['John Williams', 'Danny Elfman', 'Alan Silvestri']
            ],
            [
                'question' => 'Which animated movie features the song \'Let It Go\'?',
                'correct_answer' => 'Frozen',
                'incorrect_answers' => ['Moana', 'Tangled', 'Encanto']
            ],
            [
                'question' => 'Who directed the movie \'Inception\'?',
                'correct_answer' => 'Christopher Nolan',
                'incorrect_answers' => ['Steven Spielberg', 'Martin Scorsese', 'Quentin Tarantino']
            ],
            [
                'question' => 'Which TV show is set in the fictional town of Hawkins?',
                'correct_answer' => 'Stranger Things',
                'incorrect_answers' => ['Riverdale', 'Twin Peaks', 'Gravity Falls']
            ],
            [
                'question' => 'Who played the Joker in \'The Dark Knight\'?',
                'correct_answer' => 'Heath Ledger',
                'incorrect_answers' => ['Joaquin Phoenix', 'Jack Nicholson', 'Jared Leto']
            ],
            [
                'question' => 'Which movie franchise features the character James Bond?',
                'correct_answer' => '007',
                'incorrect_answers' => ['Mission Impossible', 'Bourne', 'Fast & Furious']
            ],
            [
                'question' => 'Who is known as the \'King of Pop\'?',
                'correct_answer' => 'Michael Jackson',
                'incorrect_answers' => ['Elvis Presley', 'Prince', 'Madonna']
            ],
            [
                'question' => 'Which band released the album \'Abbey Road\'?',
                'correct_answer' => 'The Beatles',
                'incorrect_answers' => ['The Rolling Stones', 'Led Zeppelin', 'Pink Floyd']
            ],
            [
                'question' => 'Who played Hermione Granger in the Harry Potter movies?',
                'correct_answer' => 'Emma Watson',
                'incorrect_answers' => ['Emma Stone', 'Emma Roberts', 'Emma Thompson']
            ],
            [
                'question' => 'Which video game character is known for collecting rings?',
                'correct_answer' => 'Sonic the Hedgehog',
                'incorrect_answers' => ['Mario', 'Link', 'Pac-Man']
            ],
            [
                'question' => 'Who created the animated series \'The Simpsons\'?',
                'correct_answer' => 'Matt Groening',
                'incorrect_answers' => ['Seth MacFarlane', 'Trey Parker', 'Mike Judge']
            ],
            [
                'question' => 'Which movie features the quote \'May the Force be with you\'?',
                'correct_answer' => 'Star Wars',
                'incorrect_answers' => ['Star Trek', 'Guardians of the Galaxy', 'The Matrix']
            ]
        ]
    ];

    if (!isset($questionsData[$category])) {
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        return;
    }

    $questions = $questionsData[$category];
    
    // Shuffle and select 15 questions
    shuffle($questions);
    $selectedQuestions = array_slice($questions, 0, 15);

    echo json_encode(['success' => true, 'questions' => $selectedQuestions]);
}

function saveQuizResult($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }

    $category = $_POST['category'] ?? '';
    $score = $_POST['score'] ?? 0;
    $totalQuestions = $_POST['totalQuestions'] ?? 0;
    $percentage = $_POST['percentage'] ?? 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, category, score, total_questions, percentage, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $category, $score, $totalQuestions, $percentage]);

        // Update user stats
        $stmt = $pdo->prepare("UPDATE users SET total_quizzes = total_quizzes + 1, best_score = GREATEST(best_score, ?) WHERE id = ?");
        $stmt->execute([$percentage, $_SESSION['user_id']]);

        echo json_encode(['success' => true, 'message' => 'Quiz result saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getUserStats($pdo) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT total_quizzes, best_score FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $stats = $stmt->fetch();

        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

