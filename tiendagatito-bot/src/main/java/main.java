import com.github.philippheuer.credentialmanager.domain.OAuth2Credential;
import com.github.twitch4j.TwitchClient;
import com.github.twitch4j.TwitchClientBuilder;
import com.github.twitch4j.chat.events.channel.ChannelMessageEvent;
import com.github.twitch4j.helix.domain.FollowList;
import com.github.twitch4j.helix.domain.Subscription;
import com.github.twitch4j.helix.domain.SubscriptionList;
import com.github.twitch4j.helix.domain.UserList;
import com.github.twitch4j.tmi.domain.Chatters;

import javax.swing.*;
import java.sql.*;
import java.util.Collections;
import java.util.Locale;
import java.util.StringTokenizer;

public class main {
    private static final String BOT_TOKEN = "oi2ge79iuceqp5adt1q6ur4bjip2ka";
    private static final String ACC_TOKEN = "v4wrmoi7d92pe1mz349m1h05ygiuoe";
    private static final String CLIENT_ID = "gp762nuuoqcoxypju8c569th9wz7q5";
    private static final String CHANNEL_NAME = "gatoronronyt";
    private static final String URLDB = "jdbc:mysql://smb.tiendagatito.com:3306/tiendagatito";
    private static final String DBUNAME = "website";
    private static final String DBPW = DBUNAME;
    private static String CHANNEL_ID;
    private static final int POINTS_PER_MIN = 10, FOLLOWER_MULTIPLIER = 2, T1_MULTIPLIER = 3, T2_MULTIPLIER = 5, T3_MULTIPLIER = 7;
    private static final TwitchClient client = TwitchClientBuilder.builder()
            .withClientId(CLIENT_ID)
            .withEnableHelix(true)
            .withEnableChat(true)
            .withEnableTMI(true)
            .withChatAccount(new OAuth2Credential("twitch", BOT_TOKEN))
            .withDefaultAuthToken(new OAuth2Credential("twitch", ACC_TOKEN))
            .build();

    public static void main(String[] args) {
        CHANNEL_ID = lookupUserID(CHANNEL_NAME, client);
        System.err.println(CHANNEL_ID);
        Timer t = new Timer(1 * 10 * 1000, (e) -> {
            System.err.println("Executing Update");
            Chatters c = client.getMessagingInterface().getChatters(CHANNEL_NAME).execute();
            for (String var : c.getAllViewers()) {
                String userID = lookupUserID(var, client);
                try {
                    Connection sqlConnection = DriverManager.getConnection(URLDB, DBUNAME, DBPW);
                    Statement stmt = sqlConnection.createStatement();
                    ResultSet rs = stmt.executeQuery("SELECT * FROM users where twitchID = '"+userID+"'");
                    boolean toggle = false;
                    while(rs.next()){
                        toggle = true;
                    }
                    if(!toggle){
                        sqlConnection.createStatement().executeUpdate("INSERT INTO users (twitchID, points) VALUES ('"+userID+"', 0)");
                    }
                    int pointsToAdd = 0;
                    if(!isFollowingChannel(userID, CHANNEL_ID, client)){
                        pointsToAdd = POINTS_PER_MIN;
                    }else{
                        switch(getSubscription(userID, CHANNEL_ID, client)){
                            case 0:
                                pointsToAdd = POINTS_PER_MIN * FOLLOWER_MULTIPLIER;
                                break;
                            case 1:
                                pointsToAdd = POINTS_PER_MIN * T1_MULTIPLIER;
                                break;
                            case 2:
                                pointsToAdd = POINTS_PER_MIN * T2_MULTIPLIER;
                                break;
                            case 3:
                                pointsToAdd = POINTS_PER_MIN * T3_MULTIPLIER;
                                break;
                        }
                    }
                    sqlConnection.createStatement().executeUpdate("UPDATE users set points=points+"+pointsToAdd+" WHERE twitchID = '"+userID+"'");
                } catch (SQLException throwables) {
                    throwables.printStackTrace();
                }
            }
        });
        client.getEventManager().onEvent(ChannelMessageEvent.class, (cme)->{
            String message = cme.getMessage();
            String[] arguments = null;
            boolean hasArgs = false;
            if (message.startsWith("!")) {        //is a command
                /*System.out.println("Command recognized");*/
                StringTokenizer st = new StringTokenizer(message.substring(1), " ");

                if (st.countTokens() > 1) {     //args check
                    hasArgs = true;
                    arguments = new String[st.countTokens() - 1];
                }

                String command = st.nextToken(); //separating args from command
                if (hasArgs) {
                    int i = 0;
                    while (st.hasMoreTokens()) {
                        arguments[i] = st.nextToken();
                        i++;
                    }
                }
                if (hasArgs) {
                    System.err.println("[DEBUG] Command '" + command + "' recognized. Printing arguments");
                    for (int i = 0; i < arguments.length; i++) System.err.println(arguments[i]);
                }
                switch (command.toLowerCase()) {
                    case "points":
                        String userID = cme.getUser().getId();
                        String userName = cme.getUser().getName();
                        try {
                            Connection sqlConnection = DriverManager.getConnection(URLDB, DBUNAME, DBPW);
                            Statement stmt = sqlConnection.createStatement();
                            ResultSet rs = stmt.executeQuery("SELECT * FROM users where twitchID = '"+userID+"'");
                            boolean exists = false;
                            int points = 0;
                            while(rs.next()){
                                points = rs.getInt("points");
                                exists = true;
                            }
                            if(exists)
                                client.getChat().sendMessage(CHANNEL_NAME, "@"+userName+" Tienes "+points+" puntos");
                            else
                                client.getChat().sendMessage(CHANNEL_NAME, "@"+userName+" Tienes "+0+" puntos");
                        } catch (SQLException throwables) {
                            throwables.printStackTrace();
                        }
                        break;
                }
            }
        });
        t.start();
    }

    private static String lookupUserID(String displayName, TwitchClient client) {
        try {
            UserList resultList = client.getHelix().getUsers(ACC_TOKEN, null, Collections.singletonList(displayName)).execute();
            return resultList.getUsers().get(0).getId();
        } catch (Exception e) {
            return "";
        }
    }

    private static int getSubscription(String userID, String channelID, TwitchClient client) {
        SubscriptionList sl = client.getHelix().getSubscriptionsByUser(ACC_TOKEN, channelID, Collections.singletonList(userID)).execute();
        for (Subscription subscription : sl.getSubscriptions()) {
            if (subscription.getUserId().equalsIgnoreCase(userID)) {
                String tier = subscription.getTier();
                switch (tier) {
                    case "1000":
                        return 1;
                    case "2000":
                        return 2;
                    case "3000":
                        return 3;
                }
            }
        }
        return 0;
    }

    private static boolean isFollowingChannel(String userID, String channelID, TwitchClient client) {
        FollowList fl = client.getHelix().getFollowers(ACC_TOKEN, userID, channelID, null, null).execute();
        return fl.getTotal() == 1;
    }
}
