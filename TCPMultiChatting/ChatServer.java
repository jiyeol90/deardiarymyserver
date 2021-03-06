import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.HashMap;



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
                ChatThread chatThread = new ChatThread(sock, roomManager);
                chatThread.start();
            }
        }catch (Exception e) {
            e.printStackTrace();
        }
    }
}

class ChatThread extends Thread{
    private Socket sock;
    private String initUserInfo;
    private String[] filter;
    private ChatUser user;
    private ChatRoom chatRoom;
    private RoomManager roomManager;
    private int roomIdNumber;
    //ChatUser로 묶어준다.
    private BufferedReader br;
    HashMap<ChatRoom, Integer> stackMessageInTheRoom;
    private HashMap<String, Object> hm;
    private boolean initFlag = false;
    public ChatThread(Socket sock, RoomManager roomManager) {
        this.sock = sock;
        this.roomManager = roomManager;
        //this.hm = hm;
        try {
            //PrintWriter pw = new PrintWriter(new OutputStreamWriter(sock.getOutputStream()));
            br = new BufferedReader(new InputStreamReader(sock.getInputStream(),"utf-8"));
            initUserInfo = br.readLine(); //roomId + id값을 받는다. ex) "123@james@julia" [방번호]@[내아이디]@[상대방아이디]
            filter = initUserInfo.split("@");

            String roomId = filter[0];// [roomId] 혹은 [lastRoomId/roomId] 로 온다.
            if(roomId.indexOf("lastRoomId/") == 0) {
                roomIdNumber = Integer.parseInt(roomId.split("/")[1]);
                roomIdNumber = roomIdNumber + 1; //생성할 방의 Id는 가져온 값보다 1 을 더해서 생성해 준다. 
            } else {
                roomIdNumber = Integer.parseInt(filter[0]);
            }

            String myId = filter[1];
            String friendId = filter[2];
            String roomMakeOrNot = "initConnect"; // 방을 처음 만든거면 initConnectAndMakeRoom 이라는 메시지를 보낸다.

            //기존 방에 있는 아이디가 있는지 탐색후 없을시 저장한다.
            //방이 존재하는 지는 이미 Android(client)에서 DB 쿼리후 결과값을 보낸다.(존재할 경우 roomId를 받는다.)
            //없는 경우에는 모든 방중 마지막 ID를 받아온다. -> 마지막ID + 1 한 값이 내가 생성할 방의 ID가 된다.
            //방이 있는 경우  -> [방번호]@[내아이디]@[상대방아이디]
            //방이 없는 경우 -> [lastRoomId/마지막방번호]@[내아이디]@[상대방아이디]

            //대화가 시작되고 상대방이 나갔다 다시 들어왔을때 방이 존재하는지 탐색
            chatRoom = roomManager.getRoomById(roomIdNumber);
            //처음 시작할땐 무조건 null

            if(chatRoom == null) {
                //방이 없는경우
                if(roomId.indexOf("lastRoomId/") == 0) {
                    chatRoom = new ChatRoom(roomIdNumber);
                    roomMakeOrNot += "AndMakeRoom";
                } else {
                    // 방이 있는 경우 id로 방을 만들어 줘야 할때
                    chatRoom = new ChatRoom(roomIdNumber);

                }
                //만든후 RoomManager에 알려준다.
                roomManager.addRoom(chatRoom);
                System.out.println("RoomManager에서 관리하고 있는 방 개수 : " + roomManager.roomCount());
            }

            //방이 있는경우 방에 존재하고 있는 유저를 찾는다.
            user = chatRoom.getUserByUserId(myId);
            //유저가 없는경우
            if(user == null) {
                user = new ChatUser(myId);
                user.setSock(sock);
                user.setSendTo(friendId);
                user.enterRoom(chatRoom); // 유저에 현재 입장해 있는 방을 설정한다.
                System.out.println(myId + " 객체를 생성하였습니다.");
            } else {
                System.out.println("이미 존재 하고 있는 " + myId + " 객체를 할당 합니다.");
                user.setSock(sock);
                user.setSendTo(friendId);
                System.out.println(myId + " 객체의 소켓을 새것으로 교체한다.");
            }


            System.out.println(chatRoom.getId() + "번 방에 참여한 인원수 (유저생성후 참여처리 후) : " + chatRoom.getUserSize());

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
            //chatRoom = roomManager.getRoomById(Integer.parseInt(filter[0]));
            //생성했던 방이 없는 경우
            if(chatRoom == null) {
                chatRoom = roomManager.createRoom(); //roomManager에서 방을 생성한다.

                System.out.println(chatRoom.getId() + "번 방에 참여한 인원수 : " + chatRoom.getUserSize());

                user = new ChatUser(myId); // 유저를 생성한다. (유저도 탐색후 아이디의 유저가 없을시 생성)
                user.setSock(sock); // 유저의 소켓을 설정한다.

                user.setSendTo(friendId);
                //
                //chatRoom.enterUser(user); // 생성된 방에 유저가 입장한다.
                user.enterRoom(chatRoom); // 유저에 현재 입장해 있는 방을 설정한다.
                System.out.println(chatRoom.getId() + "번 방에 참여한 인원수 (유저생성후 참여처리 후) : " + chatRoom.getUserSize());
            } else {
                //생성한 방이 있는 경우 (이미 누군가가 방을 생성하고 참여하고 있다는 의미/ 유저가 한명도 참여하지 않은 방은 없다.)
                //이 방의 상대가 내가 보내고자 하는 상대인지 판별한다.
                //맞다면 기존 대화방에서 대화 시작.
                System.out.println("내 아이디 : " + myId);
                System.out.println("내가 보내려는 상대 : " + friendId);
                System.out.println("방안의 상대가 아이디 : " + chatRoom.getUserByUserId(friendId).getUserId());
                System.out.println("방안의 상대가 보내려는 대상 : " + chatRoom.getUserByUserId(friendId).getSendTo());
                if(chatRoom.getUserByUserId(friendId).getSendTo().equals(myId)) {
                    user = new ChatUser(myId);
                    user.setSock(sock); // 유저의 소켓을 설정한다.

                    user.setSendTo(friendId);
                    user.enterRoom(chatRoom); // 유저에 현재 입장해 있는 방을 설정한다.
                    System.out.println(chatRoom.getId() + "번 방에 입장했다. 방안의 사람수 : " + chatRoom.getUserSize());
                }
            }

*/
            System.out.println("서버에서 클라이언트 당 한번만 실행되는 곳이다.");


            //채팅을 처음 시작할 때



            //기존 채팅방이 있을 경우 채팅방에 대한 정보를 검사
            /*

             */

            //위치를 바꿔준다.
            //PrintWriter pw = new PrintWriter(new OutputStreamWriter(user.getSock().getOutputStream(),"utf-8"), true);
            //roomId @ userId @ txt @ message;
            System.out.println("ubuntu에서 개행을 처리하는 방식 :"+ System.getProperty("line.separator"));


            String initMessage = chatRoom.getId() + "@"
                    + user.getUserId() + "@"
                    + roomMakeOrNot + "@"
                    + filter[1] + "님이 " + filter[0] +"번 방에 접속하셨습니다.";
            chatRoom.broadcast(initMessage);
            System.out.println(chatRoom.getId() + "번 방에 참여한 인원수 : " + chatRoom.getUserSize());
            System.out.println("접속한 사용자의 아이디 : "+ filter[1]);

//            synchronized (hm) {
//                hm.put(user.getUserId(), pw);
//            }


            initFlag = true;
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    public void run() {
        String message = null;
        String roomId = "";
        String myId = "";

        try {
           
            while((message = br.readLine()) != null) {
                if(message.contains("/&quit")) {
                    //CHATTING_ROOM_ID + "@" + MY_ID + "@" + "txt" + "@" + "/&quit";
                    String[] filter = message.split("@");
                    System.out.println(filter[0] +" 번 방에 나간다고 Broadcast를 해준다.");
                    System.out.println("message : " + message);
                    roomId = filter[0];
                    myId = filter[1];
                    break;
                }
//                if(line.indexOf("/to") == 0) {
//                    sendmsg(line);
//                }else if(line.indexOf("/room") == 0) {
//                    makeRoom(line);
//                } else if(line.indexOf("/participate") == 0) {
//                    participate(line);
//                } else if (line.indexOf("/into") == 0) {
//                    participateIn(line);
//                } else if (line.indexOf("/whereami") == 0) {
//                    printTheRoom();
//                } else if (line.indexOf("/exit") == 0) {
//                    exitRoom();
//                }
                else{
                    //sendMessage(message);
                    //message전달 형식 : roomId@userId@message => 해당
                    // filter = message.split("@");
                    System.out.println(chatRoom.getId() + "번 방에 참여한 인원수 (chatRoom.broadcast직전) : " + chatRoom.getUserSize());
                    // chatRoom.broadcast(filter[0]+"@"
                    // + filter[1] + "@"
                    // + filter[2] + "@"
                    // + filter[3] + "@"
                    // + message);
                    chatRoom.broadcast(message);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }finally {
            //유저가 방을 나갔을때
            //1. 방을 나가고 방에 사람이 있는경우 방안의 사람들에게 종료 메시지를 뿌려준다.
            //2. 방을 나가고 방에 아무도 없는 경우 방도 지워준다.
            System.out.println("finally 블로 으로 들어옴/");

            chatRoom = roomManager.getRoomById(Integer.parseInt(roomId));
            System.out.println("나갈 채팅방 id : " + chatRoom.getId());

            user = chatRoom.getUserByUserId(myId);
            System.out.println("나갈 유저 id : " + user.getUserId());

            // chatRoom.exitUser(user);

            // String exitMsg = message.replace("/&quit", "나갔습니다.");
            // chatRoom.broadcast(exitMsg);


            //Todo hm 은 사용하지 않는 방향으로 고쳐준다.
            // synchronized (hm) {
            //     hm.remove(user.getUserId());
            // }
            // broadcast(initUserInfo +"님이 접속을 종료했습니다.");
            try {
                if(sock != null) {
                    user.getSock().close();
                    chatRoom.exitUser(user);

                    System.out.println("받은 메시지 : " + message);
                    
                    String exitMsg = message.replace("/&quit", myId + "(이)가 나갔습니다.");

                    System.out.println("보낼 메시지 : " + exitMsg);
                    //System.out.println("chatRoom 안의 인원수 : " + chatRoom.getUserSize() + " 명");

                    if(chatRoom.getUserList() != null) {
                        chatRoom.broadcast(exitMsg);
                    }
                   
                }
            } catch (Exception e2) {
                e2.printStackTrace();
            }
        }
    }

//    private void sendMessage(String line) {
//
//        //message전달 형식 : roomId@userId@message => 해당
//        for(ChatUser user : chatRoom.getUserList()) {
//            if(Integer.parseInt(filter[]))
//        }
//
//
//    }




    private void exitRoom(){
        broadcast(user.getUserId() + "님이 나가셨습니다.");
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
        Object obj = hm.get(user.getUserId());
        if(obj != null) {
            PrintWriter pw = (PrintWriter)obj;
            pw.println(roomManager.getRoomList());
            pw.flush();
        }

    }

    private void participateIn(String line) {

        String roomName = line.split("/into")[1];

        if(roomManager.roomCount() > 0 ) {
            for (int i = 0; i < roomManager.roomCount(); i++) {
                if (roomManager.getRoomByIndex(i).getRoomName().equals(roomName)) {
                    chatRoom = roomManager.getRoomByIndex(i);
                    //방을 나가지 않은채 기존에 있던 방으로 다시 들어올때를 체크해줘야 한다.
                    //기존 방으로 다시 들어오면 user에 대한 setRoom만 갱신해준다.
                    if (chatRoom.getUserByUserId(user.getUserId()) != null) {
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
                    String inviteMessage = user.getUserId() + "이 참석했습니다";
                    chatRoom.broadcast(inviteMessage);
                }
            }
        }
    }

    private void makeRoom(String line) {
        String roomName = line.split("/room")[1];
        chatRoom = roomManager.createRoom();
        user.setRoom(chatRoom);
        roomManager.getRoom(chatRoom).setRoomName(roomName);
        roomManager.getRoom(chatRoom).enterUser(user);
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
                pw.println(initUserInfo + "님이 다음의 귓속말을 보내셨습니다. : " + msg2);
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
            user.getPw().println(user.getUserId() + " 님 채팅방을 선택하세요");
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