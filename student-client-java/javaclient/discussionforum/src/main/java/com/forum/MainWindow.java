package com.forum;

public class MainWindow {import java.awt.BorderLayout;
import java.awt.Color;
import java.awt.Dimension;
import java.awt.Font;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.Insets;
import java.util.LinkedHashMap;
import java.util.Map;
import javax.swing.BorderFactory;
import javax.swing.Box;
import javax.swing.BoxLayout;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.SwingUtilities;

public class SwingGroupsTopicsDemo extends JFrame {
    private final JComboBox<String> groupDropdown;
    private final JComboBox<String> topicDropdown;
    private final JPanel messagePanel;
    private final Map<String, String[]> topicsByGroup;

    public SwingGroupsTopicsDemo() {
        super("Groups and Topics");

        topicsByGroup = new LinkedHashMap<>();
        topicsByGroup.put("Programming", new String[] {"Java", "Python", "Web Design"});
        topicsByGroup.put("Science", new String[] {"Physics", "Chemistry", "Biology"});
        topicsByGroup.put("Business", new String[] {"Marketing", "Finance", "Management"});
        topicsByGroup.put("Arts", new String[] {"Music", "Painting", "Writing"});

        groupDropdown = new JComboBox<>(topicsByGroup.keySet().toArray(new String[0]));
        topicDropdown = new JComboBox<>();
        messagePanel = createMessagePanel();

        setLayout(new BorderLayout());
        add(createLeftPanel(), BorderLayout.WEST);
        add(messagePanel, BorderLayout.CENTER);

        updateTopics();
        groupDropdown.addActionListener(event -> updateTopics());

        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(800, 500);
        setLocationRelativeTo(null);
    }

    private JPanel createLeftPanel() {
        JPanel leftPanel = new JPanel();
        leftPanel.setPreferredSize(new Dimension(240, 0));
        leftPanel.setBackground(Color.BLACK);
        leftPanel.setBorder(BorderFactory.createEmptyBorder(20, 16, 20, 16));
        leftPanel.setLayout(new BoxLayout(leftPanel, BoxLayout.Y_AXIS));

        JLabel title = new JLabel("Groups");
        title.setFont(title.getFont().deriveFont(Font.BOLD, 20f));
        title.setAlignmentX(LEFT_ALIGNMENT);

        JLabel groupLabel = new JLabel("Select group");
        groupLabel.setAlignmentX(LEFT_ALIGNMENT);
        groupDropdown.setMaximumSize(new Dimension(Integer.MAX_VALUE, 32));
        groupDropdown.setAlignmentX(LEFT_ALIGNMENT);

        JLabel topicLabel = new JLabel("Select topic");
        topicLabel.setAlignmentX(LEFT_ALIGNMENT);
        topicDropdown.setMaximumSize(new Dimension(Integer.MAX_VALUE, 32));
        topicDropdown.setAlignmentX(LEFT_ALIGNMENT);

        leftPanel.add(title);
        leftPanel.add(Box.createVerticalStrut(24));
        leftPanel.add(groupLabel);
        leftPanel.add(Box.createVerticalStrut(6));
        leftPanel.add(groupDropdown);
        leftPanel.add(Box.createVerticalStrut(18));
        leftPanel.add(topicLabel);
        leftPanel.add(Box.createVerticalStrut(6));
        leftPanel.add(topicDropdown);
        leftPanel.add(Box.createVerticalGlue());

        return leftPanel;
    }

    private JPanel createMessagePanel() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBackground(Color.BLACK);
        panel.setBorder(BorderFactory.createTitledBorder("Messages"));

        JLabel placeholder = new JLabel("Message panel is empty");
        placeholder.setForeground(new Color(120, 120, 120));

        GridBagConstraints constraints = new GridBagConstraints();
        constraints.insets = new Insets(20, 20, 20, 20);
        panel.add(placeholder, constraints);

        JPanel wrapper = new JPanel(new BorderLayout());
        wrapper.setBorder(BorderFactory.createEmptyBorder(16, 16, 16, 16));
        wrapper.add(panel, BorderLayout.CENTER);

        JPanel outer = new JPanel(new BorderLayout());
        outer.add(new JScrollPane(wrapper), BorderLayout.CENTER);
        return outer;
    }

    private void updateTopics() {
        String selectedGroup = (String) groupDropdown.getSelectedItem();
        topicDropdown.removeAllItems();

        if (selectedGroup == null) {
            return;
        }

        for (String topic : topicsByGroup.get(selectedGroup)) {
            topicDropdown.addItem(topic);
        }
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> new SwingGroupsTopicsDemo().setVisible(true));
    }
}

    
}
