public class AppLauncher {


    public static void main(String[] args) {

        System.out.println("================================================");
        

        NetworkWatcher watcherTask = new NetworkWatcher();

        Thread heartbeatThread = new Thread(watcherTask);
        heartbeatThread.setName("NetworkWatcher-Heartbeat");
        heartbeatThread.setDaemon(true);
        heartbeatThread.start();

        System.out.println("[AppLauncher] NetworkWatcher running on background thread.");
        System.out.println("[AppLauncher] Disconnect your wifi to test OFFLINE detection.");
        System.out.println("------------------------------------------------");

        try {
            Thread.sleep(60000);
        } catch (InterruptedException e) {
            System.out.println("[AppLauncher] Main thread interrupted.");
        }

        System.out.println("[AppLauncher] Sprint 1 complete.");
    }
}

