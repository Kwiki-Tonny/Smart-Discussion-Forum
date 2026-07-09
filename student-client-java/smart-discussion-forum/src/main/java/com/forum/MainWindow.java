package com.forum;

import java.awt.BorderLayout;
import java.awt.CardLayout;
import java.awt.Color;
import java.awt.Component;
import java.awt.Dimension;
import java.awt.FlowLayout;
import java.awt.Font;
import java.awt.GridBagLayout;
import java.util.HashMap;
import java.util.Map;

import javax.swing.BorderFactory;
import javax.swing.Box;
import javax.swing.BoxLayout;
import javax.swing.DefaultListCellRenderer;
import javax.swing.DefaultListModel;
import javax.swing.JButton;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JList;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.ListSelectionModel;
import javax.swing.SwingUtilities;
import javax.swing.UIManager;
import javax.swing.border.MatteBorder;

import com.formdev.flatlaf.FlatLightLaf;

/**
 * MainWindow implements the synchronized Smart Discussion Forum workspace layout.
 * Architecture:
 * [Nav Rail (65px)] -> [Context Sub-Nav (280px)] -> [Workspace Fluid]
 * [Footer Panel (30px) South Anchor]
 */
public class MainWindow extends JFrame {

    // Strict Monochrome Design System Tokens
    private static final Color COLOR_RAIL_BG = new Color(0x12, 0x12, 0x12); 
    private static final Color COLOR_SURFACE = new Color(0xF9, 0xF9, 0xF9);
    private static final Color COLOR_SURFACE_CONTAINER = new Color(0xEE, 0xEE, 0xEE);
    private static final Color COLOR_SURFACE_LOWEST = new Color(0xFF, 0xFF, 0xFF);
    private static final Color COLOR_ON_SURFACE = new Color(0x1B, 0x1B, 0x1B);
    private static final Color COLOR_OUTLINE = new Color(0x7E, 0x75, 0x76);
    private static final Color COLOR_PRIMARY = Color.BLACK;
    private static final Color COLOR_ON_PRIMARY = Color.WHITE;
    private static final Color COLOR_STATUS_GREEN = new Color(0x2E, 0x7D, 0x32);

    private static final Font FONT_RAIL_ICON = new Font("SansSerif", Font.PLAIN, 22);
    private static final Font FONT_HEADER = new Font("Manrope", Font.BOLD, 13);
    private static final Font FONT_BODY_BOLD = new Font("SansSerif", Font.BOLD, 13);
    private static final Font FONT_BODY_MUTED = new Font("SansSerif", Font.PLAIN, 12);
    private static final Font FONT_FOOTER = new Font("Manrope", Font.PLAIN, 11);

    private CardLayout subNavCardLayout;
    private JPanel subNavContainer;
    private JList<String> groupList;
    private JList<String> topicList;
    private DefaultListModel<String> topicListModel;
    private JLabel subNavHeaderLabel;

    private CardLayout workspaceCardLayout;
    private JPanel workspaceContainer;
    private JLabel activeTopicTitleLabel;
    private JLabel activeTopicSnippetLabel;

    private final Map<String, String[]> groupToTopicsMap = new HashMap<>();
    private final Map<String, String> topicSnippetsMap = new HashMap<>();

    public MainWindow() {
        super("Smart Discussion Forum Workspace");
        initializeMockData();
        initUI();
    }

    private void initializeMockData() {
        groupToTopicsMap.put("Software Engineering", new String[]{
            "Assignment 1 Database Setup", "3NF Normalization Rules", "Git Merge Conflict Resolution"
        });
        groupToTopicsMap.put("Numerical Analysis", new String[]{
            "Bisection Method Convergence", "Newton-Raphson Verification"
        });
        groupToTopicsMap.put("Web Development", new String[]{
            "Laravel Filter Middleware", "Vue State Management Store"
        });

        topicSnippetsMap.put("Assignment 1 Database Setup", "Let's align our local SQLite tables to strip infrastructure overhead...");
        topicSnippetsMap.put("3NF Normalization Rules", "Ensure user profiles are decoupled into their own distinct domain spaces.");
        topicSnippetsMap.put("Bisection Method Convergence", "The root approximation algorithm hits an exception at index 24...");
        topicSnippetsMap.put("Laravel Filter Middleware", "Route exclusions must execute strictly before the main session stack mounts.");
    }

    private void initUI() {
        setSize(1150, 780);
        setMinimumSize(new Dimension(950, 650));
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);

        // Core Layout Wrapper
        JPanel masterLayoutWrapper = new JPanel(new BorderLayout());
        masterLayoutWrapper.setBackground(COLOR_SURFACE);

        // 1. EXTRACTED EXTREME LEFT GLOBAL NAVIGATION RAIL (With exact custom items)
        JPanel extremeLeftRail = createNavigationRail();
        masterLayoutWrapper.add(extremeLeftRail, BorderLayout.WEST);

        // Nested Container for Sub-Nav and Central View Workspace
        JPanel mainWorkspacePanel = new JPanel(new BorderLayout());
        mainWorkspacePanel.setBorder(BorderFactory.createEmptyBorder());

        // 2. MIDDLE SUB-NAVIGATION SYSTEM
        subNavCardLayout = new CardLayout();
        subNavContainer = new JPanel(subNavCardLayout);
        subNavContainer.setPreferredSize(new Dimension(280, getHeight()));
        subNavContainer.setBorder(new MatteBorder(0, 0, 0, 1, COLOR_OUTLINE));

        subNavContainer.add(createGroupSubNavPanel(), "GROUPS_PANEL");
        subNavContainer.add(createTopicSubNavPanel(), "TOPICS_PANEL");
        subNavCardLayout.show(subNavContainer, "GROUPS_PANEL");

        // 3. RIGHT EXPANSIVE WORKSPACE CONTAINER
        workspaceCardLayout = new CardLayout();
        workspaceContainer = new JPanel(workspaceCardLayout);
        workspaceContainer.setBackground(COLOR_SURFACE);
        
        workspaceContainer.add(createEmptyStatePanel(), "EMPTY_STATE");
        workspaceContainer.add(createActiveThreadPanel(), "THREAD_STATE");
        workspaceCardLayout.show(workspaceContainer, "EMPTY_STATE");

        mainWorkspacePanel.add(subNavContainer, BorderLayout.WEST);
        mainWorkspacePanel.add(workspaceContainer, BorderLayout.CENTER);
        masterLayoutWrapper.add(mainWorkspacePanel, BorderLayout.CENTER);

        // 4. LOWER BOUNDARY SYSTEM STATUS FOOTER (ADDED)
        JPanel systemFooter = createSystemFooterPanel();
        masterLayoutWrapper.add(systemFooter, BorderLayout.SOUTH);

        add(masterLayoutWrapper);
    }

    /**
     * Builds the vertical workspace sidebar matching your configuration.
     * Contains: Groups, Analytics, Recommendations, and a bottom-aligned Profile.
     */
    private JPanel createNavigationRail() {
        JPanel rail = new JPanel(new BorderLayout());
        rail.setBackground(COLOR_RAIL_BG);
        rail.setPreferredSize(new Dimension(65, getHeight()));
        rail.setBorder(new MatteBorder(0, 0, 0, 1, Color.BLACK));

        // Top Item Action Cluster
        JPanel topCluster = new JPanel();
        topCluster.setLayout(new BoxLayout(topCluster, BoxLayout.Y_AXIS));
        topCluster.setOpaque(false);

        // Setup exact navigation targets with specialized matching icons
        JButton btnGroups = createRailButton("👥", "Groups", true); 
        JButton btnAnalytics = createRailButton("📊", "Analytics", false);
        JButton btnRecommendations = createRailButton("💡", "Recommendations", false);

        topCluster.add(Box.createRigidArea(new Dimension(0, 15)));
        topCluster.add(btnGroups);
        topCluster.add(Box.createRigidArea(new Dimension(0, 15)));
        topCluster.add(btnAnalytics);
        topCluster.add(Box.createRigidArea(new Dimension(0, 15)));
        topCluster.add(btnRecommendations);

        // Bottom Isolated Profile Anchor Cluster
        JPanel bottomCluster = new JPanel(new BorderLayout());
        bottomCluster.setOpaque(false);
        bottomCluster.setBorder(BorderFactory.createEmptyBorder(0, 0, 15, 0));
        
        JButton btnProfile = createRailButton("👤", "Profile Profile Context", false);
        bottomCluster.add(btnProfile, BorderLayout.SOUTH);

        rail.add(topCluster, BorderLayout.NORTH);
        rail.add(bottomCluster, BorderLayout.SOUTH);
        return rail;
    }

    private JButton createRailButton(String unicodeIcon, String tooltipText, boolean isActive) {
        JButton button = new JButton(unicodeIcon);
        button.setFont(FONT_RAIL_ICON);
        button.setToolTipText(tooltipText);
        button.setPreferredSize(new Dimension(48, 48));
        button.setMaximumSize(new Dimension(48, 48));
        button.setMinimumSize(new Dimension(48, 48));
        button.setAlignmentX(Component.CENTER_ALIGNMENT);
        button.setFocusable(false);
        button.setBorderPainted(false);
        button.setContentAreaFilled(false);
        
        if (isActive) {
            button.setOpaque(true);
            button.setBackground(new Color(0x2A, 0x2A, 0x2A));
            button.setForeground(Color.WHITE);
        } else {
            button.setForeground(new Color(0x7C, 0x7C, 0x7C));
        }
        return button;
    }

    /**
     * Constructs the horizontal status footer panel tracking engine environment parameters.
     */
    private JPanel createSystemFooterPanel() {
        JPanel footer = new JPanel(new BorderLayout());
        footer.setBackground(COLOR_SURFACE_CONTAINER);
        footer.setPreferredSize(new Dimension(getWidth(), 28));
        footer.setBorder(new MatteBorder(1, 0, 0, 0, COLOR_OUTLINE));

        // Left Component: Connection Network Footprint
        JPanel leftStatusPanel = new JPanel(new FlowLayout(FlowLayout.LEFT, 10, 4));
        leftStatusPanel.setOpaque(false);
        
        JPanel liveIndicatorDot = new JPanel();
        liveIndicatorDot.setPreferredSize(new Dimension(8, 8));
        liveIndicatorDot.setBackground(COLOR_STATUS_GREEN);
        
        JLabel lblStatus = new JLabel("SYSTEM STATUS: ONLINE");
        lblStatus.setFont(FONT_FOOTER);
        lblStatus.setForeground(COLOR_ON_SURFACE);

        leftStatusPanel.add(liveIndicatorDot);
        leftStatusPanel.add(lblStatus);

        // Right Component: Target SQLite Connection Identifier
        JPanel rightDbPanel = new JPanel(new FlowLayout(FlowLayout.RIGHT, 15, 4));
        rightDbPanel.setOpaque(false);
        
        JLabel lblDatabase = new JLabel("DATABASE CONNECTION: forum.db");
        lblDatabase.setFont(FONT_FOOTER);
        lblDatabase.setForeground(COLOR_ON_SURFACE);
        rightDbPanel.add(lblDatabase);

        footer.add(leftStatusPanel, BorderLayout.WEST);
        footer.add(rightDbPanel, BorderLayout.EAST);
        return footer;
    }

    private JPanel createGroupSubNavPanel() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_SURFACE_CONTAINER);

        JLabel label = new JLabel("  GROUPS");
        label.setFont(FONT_HEADER);
        label.setPreferredSize(new Dimension(100, 45));
        label.setBorder(new MatteBorder(0, 0, 1, 0, COLOR_OUTLINE));
        label.setForeground(COLOR_ON_SURFACE);
        panel.add(label, BorderLayout.NORTH);

        DefaultListModel<String> groupModel = new DefaultListModel<>();
        for (String name : groupToTopicsMap.keySet()) {
            groupModel.addElement(name);
        }

        groupList = new JList<>(groupModel);
        groupList.setBackground(COLOR_SURFACE_LOWEST);
        groupList.setFont(FONT_BODY_BOLD);
        groupList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        groupList.setFixedCellHeight(55);
        groupList.setCellRenderer(new CleanBrutalistRenderer("📁  "));

        groupList.addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting()) {
                String group = groupList.getSelectedValue();
                if (group != null) {
                    loadTopicsPanel(group);
                }
            }
        });

        JScrollPane scrollPane = new JScrollPane(groupList);
        scrollPane.setBorder(BorderFactory.createEmptyBorder());
        panel.add(scrollPane, BorderLayout.CENTER);
        return panel;
    }

    private JPanel createTopicSubNavPanel() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_SURFACE_CONTAINER);

        JPanel headerPanel = new JPanel(new BorderLayout());
        headerPanel.setBackground(COLOR_SURFACE_CONTAINER);
        headerPanel.setBorder(new MatteBorder(0, 0, 1, 0, COLOR_OUTLINE));
        headerPanel.setPreferredSize(new Dimension(100, 45));

        JButton backBtn = new JButton("←");
        backBtn.setFont(FONT_BODY_BOLD);
        backBtn.setBackground(COLOR_PRIMARY);
        backBtn.setForeground(COLOR_ON_PRIMARY);
        backBtn.setBorder(new MatteBorder(0, 0, 0, 1, COLOR_OUTLINE));
        backBtn.setFocusable(false);
        backBtn.setPreferredSize(new Dimension(45, 45));
        
        backBtn.addActionListener(e -> {
            groupList.clearSelection();
            workspaceCardLayout.show(workspaceContainer, "EMPTY_STATE");
            subNavCardLayout.show(subNavContainer, "GROUPS_PANEL");
        });

        subNavHeaderLabel = new JLabel("  Topics");
        subNavHeaderLabel.setFont(FONT_HEADER);

        headerPanel.add(backBtn, BorderLayout.WEST);
        headerPanel.add(subNavHeaderLabel, BorderLayout.CENTER);
        panel.add(headerPanel, BorderLayout.NORTH);

        topicListModel = new DefaultListModel<>();
        topicList = new JList<>(topicListModel);
        topicList.setBackground(COLOR_SURFACE_LOWEST);
        topicList.setFont(FONT_BODY_MUTED);
        topicList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        topicList.setFixedCellHeight(48);
        topicList.setCellRenderer(new CleanBrutalistRenderer("#  "));

        topicList.addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting()) {
                String selectedTopic = topicList.getSelectedValue();
                if (selectedTopic != null) {
                    displayTopicThread(selectedTopic);
                }
            }
        });

        JScrollPane scrollPane = new JScrollPane(topicList);
        scrollPane.setBorder(BorderFactory.createEmptyBorder());
        panel.add(scrollPane, BorderLayout.CENTER);
        return panel;
    }

    private JPanel createEmptyStatePanel() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBackground(COLOR_SURFACE);

        JPanel infoCard = new JPanel();
        infoCard.setLayout(new BoxLayout(infoCard, BoxLayout.Y_AXIS));
        infoCard.setBackground(COLOR_SURFACE_LOWEST);
        infoCard.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARY, 1));
        infoCard.setPreferredSize(new Dimension(480, 160));

        JLabel title = new JLabel("NO GROUP TOPIC SELECTION ACTIVE");
        title.setFont(FONT_HEADER);
        title.setAlignmentX(Component.CENTER_ALIGNMENT);

        JLabel desc = new JLabel("Mount a localized platform thread from the sub-navigation array to audit metrics.");
        desc.setFont(FONT_BODY_MUTED);
        desc.setForeground(COLOR_OUTLINE);
        desc.setAlignmentX(Component.CENTER_ALIGNMENT);

        infoCard.add(Box.createVerticalGlue());
        infoCard.add(title);
        infoCard.add(Box.createRigidArea(new Dimension(0, 10)));
        infoCard.add(desc);
        infoCard.add(Box.createVerticalGlue());

        panel.add(infoCard);
        return panel;
    }

    private JPanel createActiveThreadPanel() {
        JPanel panel = new JPanel(new BorderLayout());
        panel.setBackground(COLOR_SURFACE);
        panel.setBorder(BorderFactory.createEmptyBorder(25, 25, 25, 25));

        JPanel threadFrame = new JPanel(new BorderLayout());
        threadFrame.setBackground(COLOR_SURFACE_LOWEST);
        threadFrame.setBorder(BorderFactory.createLineBorder(COLOR_PRIMARY, 1));

        JPanel contentPad = new JPanel();
        contentPad.setLayout(new BoxLayout(contentPad, BoxLayout.Y_AXIS));
        contentPad.setBackground(COLOR_SURFACE_LOWEST);
        contentPad.setBorder(BorderFactory.createEmptyBorder(24, 24, 24, 24));

        activeTopicTitleLabel = new JLabel("Active Thread Node");
        activeTopicTitleLabel.setFont(new Font("Manrope", Font.BOLD, 16));

        activeTopicSnippetLabel = new JLabel("Localized payload tracking details stream output parameters...");
        activeTopicSnippetLabel.setFont(FONT_BODY_MUTED);
        activeTopicSnippetLabel.setForeground(COLOR_ON_SURFACE);

        contentPad.add(activeTopicTitleLabel);
        contentPad.add(Box.createRigidArea(new Dimension(0, 15)));
        contentPad.add(activeTopicSnippetLabel);

        threadFrame.add(contentPad, BorderLayout.NORTH);
        panel.add(threadFrame, BorderLayout.CENTER);
        return panel;
    }

    private void loadTopicsPanel(String groupName) {
        topicListModel.clear();
        String[] topics = groupToTopicsMap.getOrDefault(groupName, new String[0]);
        for (String topic : topics) {
            topicListModel.addElement(topic);
        }
        subNavHeaderLabel.setText("  " + groupName.toUpperCase());
        subNavCardLayout.show(subNavContainer, "TOPICS_PANEL");
    }

    private void displayTopicThread(String topicTitle) {
        activeTopicTitleLabel.setText(topicTitle);
        activeTopicSnippetLabel.setText(topicSnippetsMap.getOrDefault(topicTitle, "No summary cache logs parsed."));
        workspaceCardLayout.show(workspaceContainer, "THREAD_STATE");
    }

    private static class CleanBrutalistRenderer extends DefaultListCellRenderer {
        private final String prefix;

        public CleanBrutalistRenderer(String prefix) {
            this.prefix = prefix;
        }

        @Override
        public Component getListCellRendererComponent(JList<?> list, Object value, int index,
                                                      boolean isSelected, boolean cellHasFocus) {
            JLabel label = (JLabel) super.getListCellRendererComponent(list, value, index, isSelected, cellHasFocus);
            label.setText("  " + prefix + value.toString());
            label.setBorder(new MatteBorder(0, 0, 1, 0, COLOR_SURFACE_CONTAINER));
            
            if (isSelected) {
                label.setBackground(COLOR_SURFACE_CONTAINER);
                label.setForeground(COLOR_PRIMARY);
            } else {
                label.setBackground(COLOR_SURFACE_LOWEST);
                label.setForeground(COLOR_ON_SURFACE);
            }
            return label;
        }
    }

    public static void main(String[] args) {
        FlatLightLaf.setup();

        UIManager.put("Button.arc", 0);
        UIManager.put("Component.arc", 0);
        UIManager.put("ScrollBar.thumbArc", 0);
        UIManager.put("ScrollBar.trackArc", 0);

        SwingUtilities.invokeLater(() -> {
            new MainWindow().setVisible(true);
        });
    }
}