const progressBar = document.querySelector(".progress-bar"),
  progressText = document.querySelector(".progress-text");

const progress = (value) => {
  const percentage = (value / time) * 100;
  progressBar.style.width = `${percentage}%`;
  progressText.innerHTML = `${value}`;
};

const startBtn = document.querySelector(".start"),
  timePerQuestion = document.querySelector("#time"),
  quiz = document.querySelector(".quiz"),
  startScreen = document.querySelector(".start-screen");

let questions = [],
  time = 30,
  score = 0,
  currentQuestion,
  timer;
  
  const startQuiz = () => {
    questions = [
      {
        question: "What is the capital of India?",
        correct_answer: "Delhi",
        incorrect_answers: ["Mumbai", "Kolkata", "Gujarat"]
      },
      {
        question: "Which planet is known as the Red Planet?",
        correct_answer: "Mars",
        incorrect_answers: ["Earth", "Venus", "Jupiter"]
      },
      {
        question: "What is the most popular sport in the world",
        correct_answer: "Football",
        incorrect_answers: ["Cricket", "Basket Ball", "Tennis"]
      },
      {
        question: "Which Indian state is known as the Land of Five Rivers",
        correct_answer:"Punjab",
        incorrect_answers:["Rajastan","Gujarat","Assam"]
      },
      {
        question:" Who was the first Indian to win an individual Olympic gold medal",
        correct_answer:"Abhinav Bindra",
        incorrect_answers:["Milkha Singh","P.T. Usha","Sachin Tendulkar"]
      },
      {
        question:"The famous cricket tournament IPL was started in which year",
        correct_answer:"2008",
        incorrect_answers:["2005","2010","2007"]
      },
      {
        question:"Which programming language is known as the Mother of all languages",
        correct_answer:"C",
        incorrect_answers:["Python","Java","Assembly"]
      },
      {
        question:"Which of the following is not an operating system",
        correct_answer:"Python",
        incorrect_answers:["Windows"," Linux","MacOS"]
      },
      {
        question:"Who is known as the Father of the Indian IT Industry",
        correct_answer:"Narayana Murthy",
        incorrect_answers:["Azim Premji","Satya Nadella","Sundar Pichai"]
      },
      {
        question:"Which organization manages domain names on the internet",
        correct_answer:" ICANN",
        incorrect_answers:["NASA","ISRO","Google"]
      },
      {
        question:"Who was the first Indian chess player to become a Grandmaster",
        correct_answer:"Viswanathan Anand",
        incorrect_answers:["Ramesh RB","Pentala Harikrishna"," Dibyendu Barua"]
      },
      {
        question:"Where is Gladiators Collosium located",
        correct_answer:"Italy",
        incorrect_answers:["Germany","USA","Africa"]
      }
    ];
  
    // Hide the start screen and show the quiz
    startScreen.classList.add("hide");
    quiz.classList.remove("hide");
  
    // Start with the first question
    currentQuestion = 1;
    showQuestion(questions[0]);
  };

startBtn.addEventListener("click", startQuiz);

const showQuestion = (question) => {
  const questionText = document.querySelector(".question"),
    answersWrapper = document.querySelector(".answer-wrapper");
  questionNumber = document.querySelector(".number");

  questionText.innerHTML = question.question;

  const answers = [
    ...question.incorrect_answers,
    question.correct_answer.toString(),
  ];
  answersWrapper.innerHTML = "";
  answers.sort(() => Math.random() - 0.5);
  answers.forEach((answer) => {
    answersWrapper.innerHTML += `
                  <div class="answer ">
            <span class="text">${answer}</span>
            <span class="checkbox">
              <i class="fas fa-check"></i>
            </span>
          </div>
        `;
  });

  questionNumber.innerHTML = ` Question <span class="current">${
    questions.indexOf(question) + 1
  }</span>
            <span class="total">/${questions.length}</span>`;
  //add event listener to each answer
  const answersDiv = document.querySelectorAll(".answer");
  answersDiv.forEach((answer) => {
    answer.addEventListener("click", () => {
      if (!answer.classList.contains("checked")) {
        answersDiv.forEach((answer) => {
          answer.classList.remove("selected");
        });
        answer.classList.add("selected");
        submitBtn.disabled = false;
      }
    });
  });

  time = timePerQuestion.value;
  startTimer(time);
};

const startTimer = (time) => {
  timer = setInterval(() => {
    if (time === 3) {
      playAdudio("countdown.mp3");
    }
    if (time >= 0) {
      progress(time);
      time--;
    } else {
      checkAnswer();
    }
  }, 1000);
};

const loadingAnimation = () => {
  startBtn.innerHTML = "Loading";
  const loadingInterval = setInterval(() => {
    if (startBtn.innerHTML.length === 10) {
      startBtn.innerHTML = "Loading";
    } else {
      startBtn.innerHTML += ".";
    }
  }, 500);
};
const submitBtn = document.querySelector(".submit"),
  nextBtn = document.querySelector(".next");
submitBtn.addEventListener("click", () => {
  checkAnswer();
});

nextBtn.addEventListener("click", () => {
  nextQuestion();
  submitBtn.style.display = "block";
  nextBtn.style.display = "none";
});

const checkAnswer = () => {
  clearInterval(timer);
  const selectedAnswer = document.querySelector(".answer.selected");
  if (selectedAnswer) {
    const answer = selectedAnswer.querySelector(".text").innerHTML;
    console.log(currentQuestion);
    if (answer === questions[currentQuestion - 1].correct_answer) {
      score++;
      selectedAnswer.classList.add("correct");
    } else {
      selectedAnswer.classList.add("wrong");
      const correctAnswer = document
        .querySelectorAll(".answer")
        .forEach((answer) => {
          if (
            answer.querySelector(".text").innerHTML ===
            questions[currentQuestion - 1].correct_answer
          ) {
            answer.classList.add("correct");
          }
        });
    }
  } else {
    const correctAnswer = document
      .querySelectorAll(".answer")
      .forEach((answer) => {
        if (
          answer.querySelector(".text").innerHTML ===
          questions[currentQuestion - 1].correct_answer
        ) {
          answer.classList.add("correct");
        }
      });
  }
  const answersDiv = document.querySelectorAll(".answer");
  answersDiv.forEach((answer) => {
    answer.classList.add("checked");
  });

  submitBtn.style.display = "none";
  nextBtn.style.display = "block";
};

const nextQuestion = () => {
  if (currentQuestion < questions.length) {
    currentQuestion++;
    showQuestion(questions[currentQuestion - 1]);
  } else {
    showScore();
  }
};

const endScreen = document.querySelector(".end-screen"),
  finalScore = document.querySelector(".final-score"),
  totalScore = document.querySelector(".total-score");
const showScore = () => {
  endScreen.classList.remove("hide");
  quiz.classList.add("hide");
  finalScore.innerHTML = score;
  totalScore.innerHTML = `/ ${questions.length}`;
};

const restartBtn = document.querySelector(".restart");
restartBtn.addEventListener("click", () => {
  window.location.reload();
});

const playAdudio = (src) => {
  const audio = new Audio(src);
  audio.play();
};