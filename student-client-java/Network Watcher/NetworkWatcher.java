import java.net.HttpURLConnection;
import java.net.URL;

public class NetworkWatcher implements Runnable {

    private static final String SERVER_PING_URL = "http://your-laravel-server/api/ping";
    private static final int CHECK_INTERVAL_MS = 10000;
    private static final int CONNECTION_TIMEOUT_MS = 3000;

    @Override
    public void run() {
        System.out.println("[NetworkWatcher] Heartbeat started.");
        System.out.println("[NetworkWatcher] Checking server every 10 seconds...");
        System.out.println("------------------------------------------------");

        while (true) {
            checkConnection();

            try {
                Thread.sleep(CHECK_INTERVAL_MS);
            } catch (InterruptedException e) {
                System.out.println("[NetworkWatcher] Heartbeat shutting down.");
                break;
            }
        }
    }

    private void checkConnection() {
        HttpURLConnection connection = null;

        try {
            URL url = new URL(SERVER_PING_URL);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setConnectTimeout(CONNECTION_TIMEOUT_MS);
            connection.setReadTimeout(CONNECTION_TIMEOUT_MS);

            int responseCode = connection.getResponseCode();

            if (responseCode == HttpURLConnection.HTTP_OK) {
                GlobalState.setOnline(true);
                System.out.println("[NetworkWatcher] Status: ONLINE");
            } else {
                GlobalState.setOnline(false);
                System.out.println("[NetworkWatcher] Status: OFFLINE (Server returned: " + responseCode + ")");
            }

        } catch (Exception e) {
            GlobalState.setOnline(false);
            System.out.println("[NetworkWatcher] Status: OFFLINE (" + e.getMessage() + ")");

        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }
}
 

