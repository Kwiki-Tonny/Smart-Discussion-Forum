public class GlobalState{

    private static volatile boolean isOnline = false;
//volatile ensures all running threads always read the latest value of isOnline
// directly from main memory, preventing threads from using outdated cached copies.
// Without volatile, one thread could update isOnline while another thread
// still sees the old value — causing the app to show wrong Online/Offline status.
//
// WHAT IS A THREAD?
// A thread is a single task running inside the app at the same time as other tasks.
// Think of it like a worker. Your app can have many workers doing different jobs
// simultaneously without waiting for each other to finish.
//
// EXAMPLE FROM THIS PROJECT:
// In our Smart Discussion Forum desktop app, we have multiple threads running at once:
//
// Thread 1 - The Heartbeat Thread:
// Every 10 seconds, this thread silently pings the server in the background
// to check if the internet is available. It then calls GlobalState.setOnline(true)
// or GlobalState.setOnline(false) based on what it finds.
//
// Thread 2 - The UI Thread:
// This thread runs the screen the student sees — the chat window, the topic list,
// the Online/Offline status banner. It reads GlobalState.isOnline() to decide
// what to show the student.
public static synchronized void setOnline(boolean status){
    isOnline = status;
}
// WHY synchronized:
// Multiple threads run at the same time in our app.
// Without synchronized, two threads could write to isOnline
// at the same moment and corrupt the value.
// synchronized puts a lock on this method — only ONE thread
// enters at a time. Others wait until it finishes.
// Think of it like a door with a lock.
// One person inside at a time. No collisions. Always accurate

public static boolean isOnline(){
    return isOnline();
}


}