import java.io.*;
import java.net.*;
import java.sql.*;
import javax.swing.*;

// This class runs in the background and checks if the internet is
// working. If it is, it sends the messages we saved while offline
// and also gets new messages from the server.
public class SyncWorker extends Thread {

    JLabel statusLabel;
    Connection localDb;
    boolean running = true;

    String serverUrl = "http://localhost:8000/api/v1/sync";

    public SyncWorker(JLabel statusLabel, Connection localDb) {
        this.statusLabel = statusLabel;
        this.localDb = localDb;
    }

    public void run() {
        while (running) {

            boolean isOnline = checkInternet();

            if (isOnline == true) {
                statusLabel.setText("Online");
                sendPendingPosts();
                getNewPosts();
            } else {
                statusLabel.setText("Offline Mode");
            }

            // wait 10 seconds then check again
            try {
                Thread.sleep(10000);
            } catch (Exception e) {
                System.out.println("Sleep was interrupted");
            }
        }
    }

    // tries to reach the server, returns true or false
    public boolean checkInternet() {
        try {
            URL url = new URL(serverUrl + "/ping");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setConnectTimeout(3000);
            connection.setRequestMethod("GET");

            int responseCode = connection.getResponseCode();

            if (responseCode == 200) {
                return true;
            } else {
                return false;
            }
        } catch (Exception e) {
            return false;
        }
    }

    // looks in the local database for posts we haven't sent yet
    public void sendPendingPosts() {
        try {
            Statement statement = localDb.createStatement();
            ResultSet result = statement.executeQuery("SELECT * FROM posts WHERE sync_status = 'pending_upload'");

            while (result.next()) {
                int postId = result.getInt("id");
                int topicId = result.getInt("topic_id");
                String content = result.getString("content");

                // build a simple text message to send to the server
                String data = "topic_id=" + topicId + "&content=" + content;

                URL url = new URL(serverUrl + "/upload");
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("POST");
                connection.setDoOutput(true);

                OutputStream out = connection.getOutputStream();
                out.write(data.getBytes());
                out.close();

                int code = connection.getResponseCode();

                if (code == 200) {
                    // mark this post as synced now
                    Statement update = localDb.createStatement();
                    update.executeUpdate("UPDATE posts SET sync_status = 'synced' WHERE id = " + postId);
                }
            }

        } catch (Exception e) {
            System.out.println("Something went wrong sending posts: " + e.getMessage());
        }
    }

    // asks the server for anything new since our last saved message
    public void getNewPosts() {
        try {
            Statement statement = localDb.createStatement();
            ResultSet result = statement.executeQuery("SELECT MAX(created_at) AS latest FROM posts");

            String lastTime = "1970-01-01 00:00:00";
            if (result.next()) {
                if (result.getString("latest") != null) {
                    lastTime = result.getString("latest");
                }
            }

            URL url = new URL(serverUrl + "/download?since=" + lastTime);
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            BufferedReader reader = new BufferedReader(new InputStreamReader(connection.getInputStream()));
            String line = "";
            String fullResponse = "";

            while ((line = reader.readLine()) != null) {
                fullResponse = fullResponse + line;
            }
            reader.close();

            // for now just printing it out, still need to save these into
            // the local database properly (will do this next)
            System.out.println("Got new data: " + fullResponse);

        } catch (Exception e) {
            System.out.println("Something went wrong getting new posts: " + e.getMessage());
        }
    }

    public void stopRunning() {
        running = false;
    }



public static void main(String[] args) {
        JLabel testLabel = new JLabel("Status: checking...");
        Connection testDb = null; // leave null for now just to see if it runs

        SyncWorker worker = new SyncWorker(testLabel, testDb);
        worker.start();

        System.out.println("Worker started, check console output...");
    }
}

    