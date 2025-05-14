// Streeties Pet Adoption Chatbot
class PetAdoptionChatbot {
  constructor() {
    this.messages = [];
    this.suggestions = [];
    this.isTyping = false;
    this.isOpen = false;
    this.createUI();
    this.initEventListeners();
    this.sendGreeting();
  }

  // Data: Questions, answers and suggestions
  get defaultSuggestions() {
    return [
      'How do I adopt a pet?',
      'What pets are available?',
      'Adoption requirements',
      'Adoption fees',
      'Volunteer opportunities'
    ];
  }

  get initialGreeting() {
    return 'Hello! I\'m Streeties\' virtual assistant. How can I help you with pet adoption today?';
  }

  get fallbackResponse() {
    return "I'm sorry, I don't have specific information about that. Would you like me to connect you with a shelter staff member?";
  }

  // Common questions and answers data
  get commonQuestions() {
    return [
      {
        id: 'q1',
        question: 'How do I adopt a pet?',
        answer: 'To adopt a pet, browse our available pets on the "Adopt" page, fill out an adoption application form, and our team will review your application. Once approved, you can schedule a meet-and-greet with your chosen pet. After a successful meeting, you can complete the adoption process by signing the adoption agreement and paying the adoption fee.',
        followUpSuggestions: [
          'What are the adoption fees?', 
          'How long does the adoption process take?', 
          'Can I meet the pet before adopting?'
        ],
      },
      {
        id: 'q2',
        question: 'What are the adoption fees?',
        answer: 'Our adoption fees vary depending on the animal. Dogs are typically $150-250, cats are $100-150, and small animals are $25-75. All adoption fees include spay/neuter, vaccinations, microchipping, and a health check. These fees help cover a portion of the care we provide to our animals.',
        followUpSuggestions: [
          'What does the adoption fee include?', 
          'Do you offer any adoption fee discounts?', 
          'How can I pay the adoption fee?'
        ],
      },
      {
        id: 'q3',
        question: 'What pets are currently available for adoption?',
        answer: 'We have a variety of dogs, cats, and occasionally other animals available for adoption. Our website\'s "Adopt" page is updated daily with all available pets. Each listing includes photos, personality descriptions, and basic information about the pet. You can filter by species, age, size, and other criteria to find your perfect match.',
        followUpSuggestions: [
          'Do you have any puppies available?', 
          'How often do you get new pets?', 
          'Are all your pets on the website?'
        ],
      },
      {
        id: 'q4',
        question: 'What are the requirements to adopt?',
        answer: 'To adopt, you must be at least 21 years old, provide a valid ID, proof of residence, and complete our adoption application. If you rent, we\'ll need landlord approval. We conduct a basic screening to ensure our pets go to suitable homes. For certain breeds or animals with specific needs, we may have additional requirements to ensure a good match.',
        followUpSuggestions: [
          'Can I adopt if I live in an apartment?', 
          'Do I need a fenced yard to adopt a dog?', 
          'Can I adopt if I have other pets?'
        ],
      },
      {
        id: 'q5',
        question: 'How long does the adoption process take?',
        answer: 'The adoption process typically takes 1-7 days. After submitting your application, we review it within 1-2 business days. If approved, you can schedule a meet-and-greet. Some adoptions can be completed same-day, while others may take longer if we need additional information or if there are multiple applications for the same pet.',
        followUpSuggestions: [
          'Can I take the pet home the same day?', 
          'What happens after my application is approved?', 
          'What if multiple people want to adopt the same pet?'
        ],
      },
      {
        id: 'q6',
        question: 'Are all your pets spayed/neutered?',
        answer: 'Yes, all our adoptable pets are spayed or neutered before going to their new homes. If you\'re adopting a young puppy or kitten that isn\'t old enough for the procedure yet, we include a spay/neuter certificate and you\'ll need to bring them back to complete the procedure when they reach the appropriate age (typically around 4-6 months).',
        followUpSuggestions: [
          'At what age do you spay/neuter?', 
          'What vaccines do your pets receive?', 
          'Do you microchip your animals?'
        ],
      },
      {
        id: 'q7',
        question: 'Can I meet the pet before adopting?',
        answer: 'Absolutely! We encourage meet-and-greets before finalizing any adoption. This allows you to interact with the pet and see if you\'re a good match. For dog adoptions where you have other dogs at home, we typically require a meet-and-greet between the dogs. Please schedule an appointment through our website or by calling our adoption center.',
        followUpSuggestions: [
          'How do I schedule a meet-and-greet?', 
          'Can I bring my family to meet the pet?', 
          'What should I expect during a meet-and-greet?'
        ],
      },
      {
        id: 'q8',
        question: 'What if the pet doesn\'t work out after adoption?',
        answer: 'We have a 14-day adjustment period policy. If the adoption doesn\'t work out within 14 days, you can return the pet and receive a refund of your adoption fee. After 14 days, we still accept returns but don\'t provide refunds. We always want our pets back rather than having them rehomed elsewhere, as we know their history and needs.',
        followUpSuggestions: [
          'What support do you offer after adoption?', 
          'Do you provide training resources?', 
          'What if my pet gets sick after adoption?'
        ],
      },
      {
        id: 'q9',
        question: 'How can I volunteer or foster?',
        answer: 'We\'re always looking for volunteers and foster homes! To volunteer, visit our website\'s "Volunteer" page and fill out an application. We offer orientation sessions twice monthly. For fostering, complete the foster application online. Fostering requires a brief home check and training session. Both programs allow you to contribute to our mission of helping homeless pets.',
        followUpSuggestions: [
          'What volunteer positions are available?', 
          'What does fostering involve?', 
          'Is there a minimum time commitment?'
        ],
      },
      {
        id: 'q10',
        question: 'What are your shelter hours?',
        answer: 'Our adoption center is open Tuesday through Friday from 12:00 PM to 7:00 PM and Saturday and Sunday from 10:00 AM to 5:00 PM. We\'re closed on Mondays for deep cleaning and staff training. Appointments are recommended for meet-and-greets, but walk-ins are welcome for general visits. Please check our website or social media for holiday hours and special events.',
        followUpSuggestions: [
          'Do I need an appointment to visit?', 
          'Where is your shelter located?', 
          'Can I come just to look at the animals?'
        ],
      }
    ];
  }

  // Utility functions
  generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substring(2);
  }

  formatMessageTime(date) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  findBestMatch(userQuestion) {
    // Convert to lowercase for case-insensitive matching
    const normalizedUserQuestion = userQuestion.toLowerCase().trim();
    
    // First try to find an exact match
    const exactMatch = this.commonQuestions.find(
      qa => qa.question.toLowerCase() === normalizedUserQuestion
    );
    
    if (exactMatch) {
      return exactMatch;
    }
    
    // Otherwise, find the question with the most keywords in common
    const wordsInUserQuestion = normalizedUserQuestion.split(/\s+/);
    
    let bestMatch = null;
    let highestMatchScore = 0;
    
    for (const qa of this.commonQuestions) {
      const questionWords = qa.question.toLowerCase().split(/\s+/);
      const matchScore = wordsInUserQuestion.filter(word => 
        questionWords.includes(word) && word.length > 3 // Only count significant words
      ).length;
      
      if (matchScore > highestMatchScore) {
        highestMatchScore = matchScore;
        bestMatch = qa;
      }
    }
    
    // Only return a match if it has a decent score
    return highestMatchScore >= 2 ? bestMatch : null;
  }

  getRandomQuestions(count = 5) {
    // Shuffle array and get a subset
    const shuffled = [...this.commonQuestions].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, count).map(q => q.question);
  }

  detectFrustration(userInput) {
    const frustrationKeywords = [
      'frustrated', 'annoyed', 'upset', 'angry', 'terrible', 'worst',
      'bad', 'awful', 'horrible', 'issue', 'problem', 'not working', 'broken',
      'useless', 'waste', 'ridiculous', 'stupid', 'disappointed'
    ];
    
    const normalizedInput = userInput.toLowerCase();
    return frustrationKeywords.some(keyword => normalizedInput.includes(keyword));
  }

  generateResponse(userInput) {
    // Handle "Try different question" specifically
    if (userInput.toLowerCase().includes("try different question") || 
        userInput.toLowerCase() === "try different question") {
      return {
        answer: "Here are some questions I can help with. Please select one or ask your own question:",
        suggestions: this.getRandomQuestions(5),
      };
    }

    // Handle "Show me popular questions" specially
    if (userInput.toLowerCase() === "show me popular questions") {
      return {
        answer: "Here are some popular questions that might help you:",
        suggestions: this.getRandomQuestions(6),
      };
    }
    
    const bestMatch = this.findBestMatch(userInput);
    
    if (bestMatch) {
      return {
        answer: bestMatch.answer,
        suggestions: bestMatch.followUpSuggestions,
      };
    }
    
    // Check for common greetings
    const greetingRegex = /^(hi|hello|hey|greetings|howdy)( there)?(!)?$/i;
    if (greetingRegex.test(userInput.trim())) {
      return {
        answer: "Hello! How can I assist you with pet adoption today?",
        suggestions: this.getRandomQuestions(5),
      };
    }
    
    // Check for thank you
    const thankYouRegex = /^(thanks|thank you|thx|ty)(!)?$/i;
    if (thankYouRegex.test(userInput.trim())) {
      return {
        answer: "You're welcome! Is there anything else I can help you with about pet adoption?",
        suggestions: ["Yes, I have another question", "No, that's all"],
      };
    }
    
    // For help or assistance requests
    const helpRegex = /^(help|assist|support|guide|options|menu)(!)?$/i;
    if (helpRegex.test(userInput.trim())) {
      return {
        answer: "I'm here to help! Here are some topics I can assist you with:",
        suggestions: this.getRandomQuestions(5),
      };
    }
    
    // Fallback for no match
    return {
      answer: this.fallbackResponse,
      suggestions: ["Speak to a representative", "Show me popular questions"],
    };
  }

  // UI functions
  createUI() {
    // Create styles
    const styles = document.createElement('style');
    styles.textContent = `
      .chatbot-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        max-width: 400px;
        width: 100%;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        z-index: 1000;
        font-family: 'Poppins', 'Open Sans', sans-serif;
        display: none;
      }

      .chatbot-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background-color: #3498db;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        transition: all 0.3s ease;
      }

      .chatbot-button:hover {
        transform: scale(1.05);
      }

      .chatbot-button i {
        color: white;
        font-size: 24px;
      }

      .chatbot-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #3498db;
        color: white;
        padding: 15px;
      }

      .chatbot-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
      }

      .chatbot-header h3 i {
        margin-right: 10px;
      }

      .chatbot-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
      }

      .chatbot-messages {
        height: 400px;
        overflow-y: auto;
        padding: 15px;
        background-color: #f8f9fa;
      }

      .chatbot-message {
        margin-bottom: 15px;
        position: relative;
        clear: both;
        max-width: 85%;
      }

      .bot-message {
        background-color: #e9f0f8;
        color: #333;
        border-radius: 10px 10px 10px 0;
        padding: 12px;
        float: left;
        margin-right: auto;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
      }

      .user-message {
        background-color: #3498db;
        color: white;
        border-radius: 10px 10px 0 10px;
        padding: 12px;
        float: right;
        margin-left: auto;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
      }

      .message-time {
        font-size: 10px;
        margin-top: 5px;
        display: block;
        text-align: right;
        opacity: 0.7;
        clear: both;
      }

      .typing-indicator {
        display: flex;
        padding: 12px;
        background-color: #e9f0f8;
        border-radius: 10px 10px 10px 0;
        width: fit-content;
        margin-bottom: 10px;
        float: left;
        clear: both;
      }

      .typing-indicator span {
        height: 8px;
        width: 8px;
        background-color: #3498db;
        border-radius: 50%;
        display: inline-block;
        margin: 0 2px;
        animation: bounce 1.3s infinite;
      }

      .typing-indicator span:nth-child(2) {
        animation-delay: 0.15s;
      }

      .typing-indicator span:nth-child(3) {
        animation-delay: 0.3s;
      }

      @keyframes bounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
      }

      .suggestion-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        margin-bottom: 15px;
        clear: both;
        width: 100%;
      }

      .suggestion-chip {
        background-color: #e9f0f8;
        color: #3498db;
        border: 1px solid #3498db;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: normal;
        text-align: center;
        word-wrap: break-word;
        margin-bottom: 5px;
      }

      .suggestion-chip:hover {
        background-color: #d4e5f7;
      }

      .chatbot-input {
        display: flex;
        padding: 10px;
        border-top: 1px solid #e9e9e9;
      }

      .chatbot-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        border-radius: 25px;
        outline: none;
      }

      .chatbot-input button {
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin-left: 10px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .chatbot-input button:disabled {
        background-color: #a3c9ea;
        cursor: not-allowed;
      }

      .chatbot-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        background-color: #f0f3f7;
        color: #666;
        font-size: 12px;
      }

      .clearfix:after {
        content: "";
        display: table;
        clear: both;
      }

      @media (max-width: 480px) {
        .chatbot-container {
          width: 90%;
          max-width: 90%;
          bottom: 10px;
          right: 5%;
          left: 5%;
        }

        .chatbot-button {
          bottom: 15px;
          right: 15px;
        }

        .chatbot-messages {
          height: 350px;
        }
        
        .suggestion-chips {
          gap: 6px;
        }
        
        .suggestion-chip {
          font-size: 13px;
          padding: 5px 10px;
          max-width: 100%;
          flex: 0 0 auto;
        }
        
        .bot-message, .user-message {
          max-width: 80%;
        }
      }
    `;
    document.head.appendChild(styles);

    // Create chatbot button
    const chatbotButton = document.createElement('div');
    chatbotButton.className = 'chatbot-button';
    chatbotButton.innerHTML = '<i class="fas fa-comment"></i>';
    chatbotButton.id = 'chatbot-launcher';
    document.body.appendChild(chatbotButton);

    // Create chatbot container
    const chatbotContainer = document.createElement('div');
    chatbotContainer.className = 'chatbot-container';
    chatbotContainer.id = 'chatbot-container';
    chatbotContainer.innerHTML = `
      <div class="chatbot-header">
        <h3><i class="fas fa-paw"></i> Pet Adoption Help</h3>
        <button class="chatbot-close" id="chatbot-close"><i class="fas fa-times"></i></button>
      </div>
      <div class="chatbot-messages" id="chatbot-messages">
        <!-- Messages will appear here -->
      </div>
      <div class="chatbot-input">
        <input type="text" id="chatbot-input-field" placeholder="Type your message here...">
        <button id="chatbot-send-button"><i class="fas fa-paper-plane"></i></button>
      </div>
      <div class="chatbot-footer">
        <small>Powered by Streeties | <i class="fas fa-heart" style="color: #ff6b6b;"></i> Helping pets find homes</small>
      </div>
    `;
    document.body.appendChild(chatbotContainer);
  }

  initEventListeners() {
    // Toggle chatbot visibility
    document.getElementById('chatbot-launcher').addEventListener('click', () => {
      this.toggleChatbot(true);
    });

    document.getElementById('chatbot-close').addEventListener('click', () => {
      this.toggleChatbot(false);
    });

    // Send message on button click
    document.getElementById('chatbot-send-button').addEventListener('click', () => {
      this.sendMessage();
    });

    // Send message on Enter key
    document.getElementById('chatbot-input-field').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        this.sendMessage();
      }
    });

    // Focus on input when chatbot opens
    document.getElementById('chatbot-container').addEventListener('transitionend', () => {
      if (this.isOpen) {
        document.getElementById('chatbot-input-field').focus();
      }
    });
  }

  toggleChatbot(open) {
    this.isOpen = open !== undefined ? open : !this.isOpen;
    const container = document.getElementById('chatbot-container');
    const button = document.getElementById('chatbot-launcher');
    
    if (this.isOpen) {
      container.style.display = 'block';
      button.style.display = 'none';
      document.getElementById('chatbot-input-field').focus();
    } else {
      container.style.display = 'none';
      button.style.display = 'flex';
    }
  }

  sendGreeting() {
    this.addMessage(this.initialGreeting, 'bot');
    this.displaySuggestions(this.defaultSuggestions);
  }

  sendMessage() {
    const inputField = document.getElementById('chatbot-input-field');
    const message = inputField.value.trim();
    
    if (!message) return;
    
    // Add user message
    this.addMessage(message, 'user');
    inputField.value = '';
    
    // Show typing indicator
    this.showTypingIndicator();
    
    // Check for frustration
    const userFrustrated = this.detectFrustration(message);
    
    // Simulate bot typing delay
    setTimeout(() => {
      this.hideTypingIndicator();
      
      // Handle "Show me popular questions" special case
      if (message.toLowerCase() === "show me popular questions") {
        const popularQuestions = this.getRandomQuestions(6);
        this.addMessage("Here are some popular questions that might help you:", 'bot');
        this.displaySuggestions(popularQuestions);
        return;
      }
      
      // Generate response
      const { answer, suggestions } = this.generateResponse(message);
      
      // Add a special message for frustrated users
      const botResponse = userFrustrated 
        ? `I understand you might be frustrated. ${answer}` 
        : answer;
      
      // Add bot message
      this.addMessage(botResponse, 'bot');
      
      // Display suggestion chips
      this.displaySuggestions(suggestions);
      
    }, Math.random() * 1000 + 1000); // Random delay between 1-2 seconds
  }

  addMessage(content, sender) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const timestamp = new Date();
    const messageId = this.generateId();
    
    const messageHTML = `
      <div class="chatbot-message ${sender}-message clearfix" id="${messageId}">
        <div class="message-content">${content}</div>
        <span class="message-time">${this.formatMessageTime(timestamp)}</span>
        <div style="clear: both;"></div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Store message
    this.messages.push({
      id: messageId,
      content,
      sender,
      timestamp
    });
  }

  showTypingIndicator() {
    this.isTyping = true;
    const messagesContainer = document.getElementById('chatbot-messages');
    
    const typingHTML = `
      <div class="typing-indicator" id="typing-indicator">
        <span></span>
        <span></span>
        <span></span>
        <div style="clear: both;"></div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  hideTypingIndicator() {
    this.isTyping = false;
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
      typingIndicator.remove();
    }
  }

  displaySuggestions(suggestions) {
    if (!suggestions || !suggestions.length) return;
    
    this.suggestions = suggestions;
    const messagesContainer = document.getElementById('chatbot-messages');
    
    // Remove previous suggestions if they exist
    const oldSuggestions = document.getElementById('suggestion-chips');
    if (oldSuggestions) {
      oldSuggestions.remove();
    }
    
    const suggestionsHTML = `
      <div class="suggestion-chips" id="suggestion-chips">
        ${suggestions.map(suggestion => 
          `<button class="suggestion-chip">${suggestion}</button>`
        ).join('')}
        <div style="clear: both;"></div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', suggestionsHTML);
    
    // Add event listeners to suggestion chips
    document.querySelectorAll('.suggestion-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        document.getElementById('chatbot-input-field').value = chip.textContent;
        this.sendMessage();
      });
    });
    
    // Ensure scroll to the latest content
    setTimeout(() => {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }, 50);
  }
}

// Initialize the chatbot when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  window.petAdoptionChatbot = new PetAdoptionChatbot();
}); 