import javax.swing.*;
import java.awt.*;
import java.awt.event.*;
import java.net.*;
import java.io.*;
import java.time.*;

// This is the quiz window that pops up when the quiz starts.
// It stays on top of everything and does not let the student
// close it. It also counts down the time left.
public class QuizWindow extends JDialog {


    JLabel timerLabel;
    Timer countdownTimer;
    LocalDateTime endTime;
    boolean alreadySubmitted = false;

    public QuizWindow(Frame owner, LocalDateTime endTime) {
        super(owner, "Quiz In Progress", true);
        this.endTime = endTime;

        setUndecorated(true);
        setAlwaysOnTop(true);
        setSize(600, 400);
        setLocationRelativeTo(owner);
        setLayout(new BorderLayout());

        timerLabel = new JLabel("Time Remaining: --:--");
        timerLabel.setHorizontalAlignment(SwingConstants.CENTER);
        timerLabel.setFont(new Font("Arial", Font.BOLD, 20));
        add(timerLabel, BorderLayout.NORTH);

        JPanel questionsPanel = new JPanel();
        questionsPanel.add(new JLabel("Quiz questions go here"));
        add(questionsPanel, BorderLayout.CENTER);

        JButton submitButton = new JButton("Submit");
        submitButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                submitQuiz(false);
            }
        });
        add(submitButton, BorderLayout.SOUTH);

        watchForFocusLoss();
        startTimer();
    }

    // if the student clicks away from the quiz window, this catches it
    public void watchForFocusLoss() {
        this.addWindowFocusListener(new WindowFocusListener() {
            public void windowLostFocus(WindowEvent e) {
                Toolkit.getDefaultToolkit().beep();
                getContentPane().setBackground(Color.RED);
                System.out.println("Warning: student left the quiz window");
                sendWarningToServer();
            }

            public void windowGainedFocus(WindowEvent e) {
                getContentPane().setBackground(Color.WHITE);
            }
        });
    }

    public void sendWarningToServer() {
        try {
            URL url = new URL("http://localhost:8000/api/v1/quiz/flag");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setDoOutput(true);
            OutputStream out = connection.getOutputStream();
            out.write("event=focus_lost".getBytes());
            out.close();
            connection.getResponseCode();
        } catch (Exception e) {
            System.out.println("Could not send warning: " + e.getMessage());
        }
    }

    // counts down every second using a basic Swing timer
    public void startTimer() {
        countdownTimer = new Timer(1000, new ActionListener() {
            public void actionPerformed(ActionEvent e){
                long secondsLeft = endTime.toEpochSecond(ZoneOffset.UTC)
        - LocalDateTime.now().toEpochSecond(ZoneOffset.UTC);
            

                if (secondsLeft <= 0) {
                    timerLabel.setText("Time Remaining: 00:00");
                    countdownTimer.stop();
                    submitQuiz(true);
                    return;
                }

                long minutes = secondsLeft / 60;
                long seconds = secondsLeft % 60;

                String minuteText = "" + minutes;
                String secondText = "" + seconds;
                if (seconds < 10) {
                    secondText = "0" + seconds;
                }

                timerLabel.setText("Time Remaining: " + minuteText + ":" + secondText);
            }
        });
        countdownTimer.start();
    }
    // called either when the student clicks submit, or when time runs out
    public void submitQuiz(boolean timeRanOut) {
        if (alreadySubmitted == true) {
            return;
        }
        alreadySubmitted = true;

        // turn off the question panel so nothing else can be changed
        Component questionsPanel = getContentPane().getComponent(1);
        questionsPanel.setEnabled(false);

        if (timeRanOut == true) {
            JOptionPane.showMessageDialog(this, "Time Expired. Submitting...");
        }

        // still need to grab the real answers from the form, using a
        // placeholder for now
        sendAnswersToServer("answers=none_yet");

        countdownTimer.stop();
        this.dispose();
    }

    public void sendAnswersToServer(String answerData) {
        try {
            URL url = new URL("http://localhost:8000/api/v1/quiz/submit");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setDoOutput(true);
            OutputStream out = connection.getOutputStream();
            out.write(answerData.getBytes());
            out.close();
            connection.getResponseCode();
        } catch (Exception e) {
            System.out.println("Could not submit answers: " + e.getMessage());
        }
    }

public static void main(String[] args) {
    JFrame ownerFrame = new JFrame(); // QuizWindow needs an owner frame to attach to

    // set the quiz to end 30 seconds from now, just to test the countdown
    LocalDateTime testEndTime = LocalDateTime.now().plusSeconds(30);

    QuizWindow quiz = new QuizWindow(ownerFrame, testEndTime);
    quiz.setVisible(true); // this is the line that actually makes it appear

    System.out.println("Quiz window should be showing now...");
}
}
