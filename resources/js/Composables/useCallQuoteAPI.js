import {ref} from "vue";

export function CallQuoteAPI(quote) {
    const fallbackQuotes = [
        { content: "Success is not final, failure is not fatal: it is the courage to continue that counts.", author: "Winston Churchill" },
        { content: "The only way to do great work is to love what you do.", author: "Steve Jobs" },
        { content: "Don't judge each day by the harvest you reap but by the seeds that you plant.", author: "Robert Louis Stevenson" },
        { content: "The future depends on what you do today.", author: "Mahatma Gandhi" },
        { content: "It always seems impossible until it's done.", author: "Nelson Mandela" },
        { content: "Quality means doing it right when no one is looking.", author: "Henry Ford" },
        { content: "Strive not to be a success, but rather to be of value.", author: "Albert Einstein" },
        { content: "Great things are done by a series of small things brought together.", author: "Vincent Van Gogh" }
    ];

    // Select a random quote
    const randomIndex = Math.floor(Math.random() * fallbackQuotes.length);
    quote.value = fallbackQuotes[randomIndex];
}
