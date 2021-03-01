
import java.net.*;               
import java.io.*;               
import java.util.*;                
               
public class MultiChatServer {                
               
    public static void main(String[] args) {           
        try{       
            ServerSocket server = new ServerSocket(10001);   
            System.out.println("접속을 기다립니다.");   
            HashMap<String, Object> hm = new HashMap<String, Object>();   
            while(true){   
                Socket sock = server.accept();
                ChatThread chatthread = new ChatThread(sock, hm);
                chatthread.start();
            } // while   
        }catch(Exception e){   
            System.out.println(e);
        }   
    } // main       
}            
           
class ChatThread extends Thread{           
    private Socket sock;       
    private String id;       
    private BufferedReader br;       
    private HashMap<String, Object> hm;       
    private boolean initFlag = false;    
    
    private DataInputStream dis; 
    //$dir = $_SERVER['DOCUMENT_ROOT']."/uploads/post_images/"; // $_SERVER['DOCUMENT_ROOT'] => /var/www/html
    private FileOutputStream fos;
    private DataOutputStream dos;
    public ChatThread(Socket sock, HashMap<String, Object> hm){       
        this.sock = sock;   
        this.hm = hm;   
        try{   
            PrintWriter pw = new PrintWriter(new OutputStreamWriter(sock.getOutputStream()));   
            br = new BufferedReader(new InputStreamReader(sock.getInputStream()));   
            id = br.readLine();   
           
            
            synchronized(hm){   
                hm.put(this.id, pw);
            }   
            broadcast("initConnect:" + id + "님이 접속하였습니다."); 
            System.out.println("broadcast가 되었다.");
            System.out.println("접속한 사용자의 아이디는 " + id + "입니다.");   

            initFlag = true;   
        }catch(Exception ex){       
            System.out.println(ex);   
        }       
    } // 생성자   

    public void run(){           
        try{       
            String line = null;   
            while((line = br.readLine()) != null){       
                if(line.equals("/quit"))   
                    break;
                if(line.indexOf("/to") == 0){   
                    sendmsg(line);
                }
                if(line.indexOf("img@") == 0) {
                    String imgPath = line.split("img@")[1];
                    System.out.println("image 파일을 전달해 준다는 신호.");
                    broadcast("img" + ":" + imgPath +":"+ id);
                }
                else   
                    broadcast(id + " : " + line);
            }       
        }catch(Exception ex){           
            System.out.println(ex);       
        }finally{           
            synchronized(hm){       
                hm.remove(id);   
            }       
            broadcast("disconnect:"+ id + " 님이 접속 종료하였습니다.");       
            try{       
                if(sock != null)   
                    sock.close();
            }catch(Exception ex){}       
        }           
    } // run   

    public void sendimg() {
        System.out.println("image 파일을 전달받는 sendimg()로 들어옴.");
     try{   
        UUID fileName = UUID.randomUUID();

        File file = new File("/var/www/html/uploads/chatting_images/" + fileName);

        if (!file.exists()) {
            try {
                file.createNewFile();
                System.out.println("파일생성에 성공.");
            } catch (IOException e) {
                System.out.println("파일생성에 실패하였습니다.");
            }
        }

        fos = new FileOutputStream(file);
        byte[] buf = new byte[1024]; 
                 
        int readBytes;

        int i = 0;
        
        while ((readBytes = sock.getInputStream().read(buf)) != -1) { //보낸것을 딱맞게 받아 write 합니다.
                fos.write(buf, 0, readBytes);
               
            //System.out.println("image 파일을 전달받고 있다. " + "[" + i++ + "]" );
              }

            // broadcast("img:"+fileName.toString());
            System.out.println("수신완료");

            fos.close();
          
       
    } catch (IOException e) {
        e.printStackTrace();
    }
}


    public void sendmsg(String msg){               
        int start = msg.indexOf(" ") +1;           
        int end = msg.indexOf(" ", start);           
        if(end != -1){           
            String to = msg.substring(start, end);       
            String msg2 = msg.substring(end+1);       
            Object obj = hm.get(to);       
            if(obj != null){       
                PrintWriter pw = (PrintWriter)obj;   
                pw.println(id + " 님이 다음의 귓속말을 보내셨습니다. :" + msg2);   
                pw.flush();   
            } // if   
        }       
    } // sendmsg  
    
    
    public void broadcast(String msg){           
        synchronized(hm){       
            System.out.println("broadcast된 메시지 : " + msg);
            Collection<Object> collection = hm.values();   
            Iterator<Object> iter = collection.iterator();   
            while(iter.hasNext()){
                PrintWriter pw = (PrintWriter)iter.next();
                System.out.println("while문 안에서: " +   msg);
                pw.println(msg);
                pw.flush();
            }   
        }       
    } // broadcast           
}               
