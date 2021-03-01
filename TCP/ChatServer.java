import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.charset.StandardCharsets;
import java.util.Collection;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Scanner;


public class ChatServer {
    public static void main(String[] args) {
        try {
            ServerSocket server = new ServerSocket(10001);
            RoomManager roomManager = new RoomManager();//RoomManager는 처음 하나만 생성한다.
            
            // HashMap<String, Object> hm = new HashMap<String, Object>();
            //유저 ID와 PrintWriter를 담아 놓는다. -> 방 ID와 User클래스를 담아둘 Hashmap으로 생성?

            while(true) {
                System.out.println("접속을 기다립니다.");
                Socket sock = server.accept();
                ChatThread chatThread = new ChatThread(sock);
                chatThread.start();
            }
        }catch (Exception e) {
            e.printStackTrace();
        }
    }
}

class ChatThread extends Thread{
    private Socket sock;
    private String id;
    private ChatUser user;
    private ChatRoom chatRoom;
    private RoomManager roomManager;
    //ChatUser로 묶어준다.
    private BufferedReader br;
    HashMap<ChatRoom, Integer> stackMessageInTheRoom;
    private HashMap<String, Object> hm;
    private boolean initFlag = false;
    public ChatThread(Socket sock) {
        this.sock = sock;
        //this.hm = hm;
        this.roomManager = roomManager;
        try {
            //PrintWriter pw = new PrintWriter(new OutputStreamWriter(sock.getOutputStream()));
            br = new BufferedReader(new InputStreamReader(sock.getInputStream(),"utf-8"));
            id = br.readLine(); //id값을 받는다.

            user = new ChatUser(id); // 유저를 생성한다.
            user.setSock(sock); // 유저의 소켓을 설정한다.

            chatRoom = createRoom().enterUser(user); //roomManager에서 방을 생성한다.
            



            //위치를 바꿔준다.
            PrintWriter pw = new PrintWriter(new OutputStreamWriter(user.getSock().getOutputStream(),"utf-8"), true);

            broadcast(id + "님이 접속하셨습니다.");
            System.out.println("접속한 사용자의 아이디 : "+id);
            synchronized (hm) {
                hm.put(user.getNickName(), pw);
            }
            initFlag = true;
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    public void run() {
        try {
            String line = null;
            while((line = br.readLine()) != null) {
                if(line.equals("/quit")) {
                    break;
                }
                if(line.indexOf("/to") == 0) {
                    sendmsg(line);
                }else if(line.indexOf("/room") == 0) {
                    makeRoom(line);
                } else if(line.indexOf("/participate") == 0) {
                    participate(line);
                } else if (line.indexOf("/into") == 0) {
                    participateIn(line);
                } else if (line.indexOf("/whereami") == 0) {
                    printTheRoom();
                } else if (line.indexOf("/exit") == 0) {
                    exitRoom();
                }
                else{
                    broadcast(user.getNickName()+" : "+line);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }finally {
            //유저가 방을 나갔을때
            //1. 방을 나가고 방에 사람이 있는경우 방안의 사람들에게 종료 메시지를 뿌려준다.
            //2. 방을 나가고 방에 아무도 없는 경우 방도 지워준다.
            chatRoom = user.getRoom();
            chatRoom.exitUser(user);

            broadcast(user.getNickName() + "님이 나가셨습니다.");


            //Todo hm 은 사용하지 않는 방향으로 고쳐준다.
            synchronized (hm) {
                hm.remove(user.getNickName());
            }
            broadcast(id+"님이 접속을 종료했습니다.");
            try {
                if(sock != null) {
                    user.getSock().close();
                }
            } catch (Exception e2) {
                e2.printStackTrace();
            }
        }
    }

    private void exitRoom(){
        broadcast(user.getNickName() + "님이 나가셨습니다.");
        chatRoom = user.getRoom();
        chatRoom.exitUser(user);


    }

    private void printTheRoom () {
        if(user.getRoom() != null) {
            user.getPw().println(user.getRoom().getRoomName());
        } else {
            user.getPw().println("참여한 방이 없습니다.\n");
        }
    }

    private void participate(String line) {
        Object obj = hm.get(user.getNickName());
        if(obj != null) {
            PrintWriter pw = (PrintWriter)obj;
            pw.println(RoomManager.getRoomList());
            pw.flush();
        }

    }

    private void participateIn(String line) {

        String roomName = line.split("/into")[1];

        if(RoomManager.roomCount() > 0 ) {
            for (int i = 0; i < RoomManager.roomCount(); i++) {
                if (RoomManager.getRoomByIndex(i).getRoomName().equals(roomName)) {
                    chatRoom = RoomManager.getRoomByIndex(i);
                    //방을 나가지 않은채 기존에 있던 방으로 다시 들어올때를 체크해줘야 한다.
                    //기존 방으로 다시 들어오면 user에 대한 setRoom만 갱신해준다.
                    if (chatRoom.getUserByNickName(user.getNickName()) != null) {
                        user.setRoom(chatRoom);
                    } else {
                        //exit으로 나갔다가 다시 들어온 경우
                        chatRoom.enterUser(user);
                        user.setRoom(chatRoom);
                        //stackMessageInTheRoom을 다시 설정해 준다.
                        stackMessageInTheRoom = user.getStackMessageInTheRoom();
                        stackMessageInTheRoom.put(chatRoom, user.getStackMessage());
                        user.setStackMessageInTheRoom(stackMessageInTheRoom);

                    }
                    String inviteMessage = user.getNickName() + "이 참석했습니다";
                    chatRoom.broadcast(inviteMessage);
                }
            }
        }
    }

    private void makeRoom(String line) {
        String roomName = line.split("/room")[1];
        chatRoom = RoomManager.createRoom();
        user.setRoom(chatRoom);
        RoomManager.getRoom(chatRoom).setRoomName(roomName);
        RoomManager.getRoom(chatRoom).enterUser(user);
        //System.out.println(roomManager.getRoomList());
        //룸을 만든 사람에게만 출력해준다.

//        Object obj = hm.get(user.getNickName());
//        if(obj != null) {
//            PrintWriter pw = (PrintWriter)obj;
//            pw.println(roomManager.getRoomList() + "\r\n" + roomName + " 에 참여하였습니다.");
//            pw.flush();
//        }
        user.getPw().println(roomManager.getRoomList() + "\r\n" + roomName + " 에 참여하였습니다.");
        user.getPw().flush();

        stackMessageInTheRoom = user.getStackMessageInTheRoom();
        stackMessageInTheRoom.put(chatRoom, user.getStackMessage());
        user.setStackMessageInTheRoom(stackMessageInTheRoom);
    }

    public void sendmsg(String msg) {
        int start = msg.indexOf(" ") + 1;
        int end = msg.indexOf(" ",start);
        if(end != -1) {
            String to = msg.substring(start, end);
            String msg2 = msg.substring(end +1);
            Object obj = hm.get(to);
            if(obj != null) {
                PrintWriter pw = (PrintWriter)obj;
                pw.println(id + "님이 다음의 귓속말을 보내셨습니다. : " + msg2);
                pw.flush();
            }
        }
    }
    public void broadcast(String msg) {
        //룸이 존재하면 룸에 들어온 상대에게만 메시지를 보내고 받는다.
        //문제점 : 방에 있지 않은 사람은 모든 사람에게 메시지를 보낸다.
        if(roomManager.roomCount() > 0 && user.getRoom() != null) {
            chatRoom = user.getRoom();// 채팅룸을 구한다.
            chatRoom.broadcast(msg);
        } else {
            user.getPw().println(user.getNickName() + " 님 채팅방을 선택하세요");
//            synchronized (hm) {
//                Collection<Object> collection = hm.values();
//                Iterator iter = collection.iterator();
//                while (iter.hasNext()) {
//                    PrintWriter pw = (PrintWriter) iter.next();
//                    pw.println(msg);
//                    pw.flush();
//                }
//            }
        }
    }
}
